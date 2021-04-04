<?php

namespace Rocket\Command;

use Rocket\Chunker;
use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Main;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Process;
use Rocket\ProcessEvents;
use Rocket\Slack;
use Rocket\Slack\BlockKit\Block\Context as SlackContext;
use Rocket\Slack\BlockKit\Block\Divider as SlackDivider;
use Rocket\Slack\BlockKit\Block\Header as SlackHeader;
use Rocket\Slack\BlockKit\Block\Section as SlackSection;
use Rocket\Slack\BlockKit\Element\MarkdownText as SlackMarkdownText;
use Rocket\Slack\BlockKit\Element\PlainText as SlackPlainText;
use Rocket\Slack\BlockKit\Message as SlackMessage;

class DeployCommand implements CommandInterface
{
    /** @var Options */
    private $options;

    /** @var OutputInterface */
    private $output;

    public function __construct(Options $options, OutputInterface $output)
    {
        $this->options = $options;
        $this->output = $output;
    }

    public function execute()
    {
        $configPath = realpath($this->options->getConfig());
        $configure = new Configure($configPath);

        $chunker = new Chunker();

        $self = $this;

        if (posix_getpwuid(posix_geteuid())['name'] !== $configure->read('user')) {
            $this->output->error('can not executed user.');

            return;
        }

        // GIT
        $gitPullLog = null;
        if ($this->options->getGit() === 'pull') {
            $dir = $configure->read('source.directory');
            if (! is_dir($dir)) {
                $this->output->error($dir . ': no such file or directory');

                return;
            }

            // GIT REMOTE SHOW
            $checkProcess = Process::define($configure->read('git.path', '/usr/bin/git'));
            $checkProcess
                ->addArgument('--git-dir', $dir . '.git', '=')
                ->addArgument('--work-tree', $dir, '=')
                ->addArgument('remote')
                ->addArgument('show')
                ->addArgument('origin')
                ->setSubscribeEvent(ProcessEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                    /** @var Process $command */
                    if ($self->options->hasDebug()) {
                        $self->output->debug('$ ' . $command->string());
                    }
                })
                ->execute();

            if (! $checkProcess->isSuccess()) {
                $this->output->plain('$ ' . $checkProcess->string());
                $this->output->error($checkProcess->getOutputString());

                return;
            }

            $this->output->plain('$ ' . $checkProcess->string());
            $this->output->plain($checkProcess->getOutputString());

            if (strpos($checkProcess->getOutputString(), 'local out of date') === false) {
                $this->output->info('the directory is up to date.');

                return;
            }

            // GIT PULL WITH PRUNE
            $gitPullProcess = Process::define($configure->read('git.path', '/usr/bin/git'));
            $gitPullProcess
                ->addArgument('--git-dir', $dir . '.git', '=')
                ->addArgument('--work-tree', $dir, '=')
                ->addArgument('pull')
                ->addArgument('--prune')
                ->setSubscribeEvent(ProcessEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                    /** @var Process $command */
                    if ($self->options->hasDebug()) {
                        $self->output->debug('$ ' . $command->string());
                    }
                })
                ->execute();

            if (! $gitPullProcess->isSuccess()) {
                $this->output->plain('$ ' . $gitPullProcess->string());
                $this->output->error($gitPullProcess->getOutputString());

                return;
            }

            $this->output->plain('$ ' . $gitPullProcess->string());
            $this->output->info($gitPullProcess->getOutputString());

            $gitPullLog = $gitPullProcess->getOutputString();
        }

        // FILE SYNC
        $syncLog = null;
        $isFileChanged = false;
        if ($this->options->hasSync()) {
            $sync = $this->options->getSync();
            $destinations = $configure->read('destinations');
            foreach ($destinations as $destination) {
                $from = $destination['from'];
                $to = $destination['to'];
                $excludes = isset($destination['excludes']) ? $destination['excludes'] : [];
                $scripts = isset($destination['scripts']) ? $destination['scripts'] : [];

                $rsyncProcess = Process::define($configure->read('rsync.path', '/usr/bin/rsync'));
                $rsyncProcess
                    ->addArgument($configure->read('rsync.option'))
                    ->addArgument($from)
                    ->addArgument($to)
                    ->setSubscribeEvent(ProcessEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                        /** @var Process $command */
                        if ($self->options->hasDebug()) {
                            $self->output->debug('$ ' . $command->string());
                        }
                    });

                foreach ($excludes as $exclude) {
                    $rsyncProcess->addArgument('--exclude', $exclude, '=');
                }

                $isError = false;

                switch ($sync) {
                    case 'dry':
                        $rsyncProcess
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($rsyncProcess->isSuccess()) {
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->info($rsyncProcess->getOutputString());
                        } else {
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->error($rsyncProcess->getOutputString());
                        }

                        break;

                    case 'force':
                        $rsyncProcess
                            ->execute();
                        if ($rsyncProcess->isSuccess()) {
                            $syncLog .= $rsyncProcess->getOutputString();
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->info($rsyncProcess->getOutputString());
                        } else {
                            $isError = true;
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->error($rsyncProcess->getOutputString());
                        }

                        break;

                    case 'confirm':
                        $rsyncDryProcess = clone $rsyncProcess;

                        $rsyncDryProcess
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($rsyncDryProcess->isSuccess()) {
                            $this->output->plain('$ ' . $rsyncDryProcess->string());
                            $this->output->info($rsyncDryProcess->getOutputString());
                        } else {
                            $isError = true;
                            $this->output->plain('$ ' . $rsyncDryProcess->string());
                            $this->output->error($rsyncDryProcess->getOutputString());
                        }

                        echo 'Do you want to synchronize? [y/N]' . PHP_EOL;
                        if (trim(fgets(STDIN)) === 'y') {
                            $rsyncProcess->execute();
                            if ($rsyncProcess->isSuccess()) {
                                $syncLog .= $rsyncProcess->getOutputString();
                                $this->output->plain('$ ' . $rsyncProcess->string());
                                $this->output->info($rsyncProcess->getOutputString());
                            } else {
                                $isError = true;
                                $this->output->plain('$ ' . $rsyncProcess->string());
                                $this->output->error($rsyncProcess->getOutputString());
                            }
                        }

                        break;
                }

                if ($sync === 'dry' || $isError) {
                    continue;
                }

                if ($rsyncProcess->getOutput() !== null) {
                    foreach ($rsyncProcess->getOutput() as $output) {
                        if (preg_match('/^building file/', $output) === 1 ||
                            preg_match('/^sent \d+ bytes/', $output) === 1 ||
                            preg_match('/^total size/', $output) === 1 ||
                            $output === '') {
                            continue;
                        }

                        $isFileChanged = true;
                    }
                }

                foreach ($scripts as $script) {
                    $customScriptProcess = Process::define($script['path']);
                    $customScriptProcess
                        ->addArgument($script['option'])
                        ->setSubscribeEvent(ProcessEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                            /** @var Process $command */
                            if ($self->options->hasDebug()) {
                                $self->output->debug('$ ' . $command->string());
                            }
                        })
                        ->execute();

                    if ($customScriptProcess->isSuccess()) {
                        $this->output->info($customScriptProcess->path() . ': ' . $customScriptProcess->getOutputString());
                    } else {
                        $this->output->error($customScriptProcess->getOutputString());
                    }
                }
            }
        }

        // FILE CHANGE NOTIFICATION
        if (! $isFileChanged) {
            return;
        }

        $message = new SlackMessage();
        $message
            ->addBlock(
                new SlackHeader(new SlackPlainText('Deploy successful'))
            )
            ->addBlock(
                (new SlackSection())->setText(
                    new SlackPlainText(get_current_user() . ' was deployed :simple_smile:')
                )
            )
            ->addBlock(
                (new SlackSection())
                    ->addField(
                        new SlackMarkdownText('*Hostname:*' . PHP_EOL . gethostname())
                    )
                    ->addField(
                        new SlackMarkdownText('*URL:*' . PHP_EOL . $configure->read('url'))
                    )
            );

        if ($gitPullLog !== null) {
            $message
                ->addBlock(
                    new SlackDivider()
                )
                ->addBlock(
                    (new SlackSection())
                        ->addField(
                            new SlackMarkdownText('*Git pull*')
                        )
                );

            $chunks = $chunker($gitPullLog, SlackSection::TEXT_MAX_LENGTH - 6);
            foreach ($chunks as $chunk) {
                $message
                    ->addBlock(
                        (new SlackSection())
                            ->setText(
                                new SlackMarkdownText('```' . $chunk . '```')
                            )
                    );
            }
        }

        if ($syncLog !== null) {
            $message
                ->addBlock(
                    new SlackDivider()
                )
                ->addBlock(
                    (new SlackSection())
                        ->addField(
                            new SlackMarkdownText('*Rsync*')
                        )
                );

            $chunks = $chunker($syncLog, SlackSection::TEXT_MAX_LENGTH - 6);
            foreach ($chunks as $chunk) {
                $message
                    ->addBlock(
                        (new SlackSection())
                            ->setText(
                                new SlackMarkdownText('```' . $chunk . '```')
                            )
                    );
            }
        }

        $message
            ->addBlock(
                new SlackDivider()
            )
            ->addBlock(
                (new SlackContext())
                    ->addElement(
                        new SlackMarkdownText('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new SlackMarkdownText('Version: ' . Main::appName() . ' ' . Main::VERSION)
                    )
                    ->addElement(
                        new SlackMarkdownText('Configuration: ' . $configure->getConfigPath())
                    )
            );

        $slack = new Slack(
            $configure->read('slack.incomingWebhook'),
            $configure->read('slack.channel'),
            $configure->read('slack.username')
        );
        $slackResult = $slack->send($message);
        if (! $slackResult->isOk()) {
            $this->output->error($slackResult->getError());
        }
    }
}
