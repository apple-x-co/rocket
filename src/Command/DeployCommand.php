<?php

namespace Rocket\Command;

use Rocket\Chunker;
use Rocket\CommandInterface;
use Rocket\Configure;
use Rocket\Http;
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
use Rocket\Version;
use RuntimeException;

class DeployCommand implements CommandInterface
{
    /** @var Options */
    private $options;

    /** @var OutputInterface */
    private $output;

    /** @var Http */
    private $http;

    public function __construct(Options $options, OutputInterface $output, Http $http)
    {
        $this->options = $options;
        $this->output = $output;
        $this->http = $http;
    }

    public function execute()
    {
        $configPath = realpath($this->options->getConfig());
        if (! is_string($configPath)) {
            throw new RuntimeException();
        }

        $configure = new Configure($configPath);

        $userinfo = posix_getpwuid(posix_geteuid());
        if (! is_array($userinfo) || ! isset($userinfo['name'])) {
            throw new RuntimeException();
        }

        if ($userinfo['name'] !== $configure->read('user')) {
            $this->output->error('can not executed user.');

            return;
        }

        $chunker = new Chunker();

        $self = $this;

        // GIT
        $gitPullLog = null;
        if ($this->options->getGit() === 'pull') {
            $dir = $configure->read('source.directory');
            if (! is_string($dir)) {
                throw new RuntimeException();
            }

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
                $this->output->error($checkProcess->getOutputAsString());

                return;
            }

            $this->output->plain('$ ' . $checkProcess->string());
            $this->output->plain($checkProcess->getOutputAsString());

            if (strpos($checkProcess->getOutputAsString(), 'local out of date') === false) {
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
                $this->output->error($gitPullProcess->getOutputAsString());

                return;
            }

            $this->output->plain('$ ' . $gitPullProcess->string());
            $this->output->info($gitPullProcess->getOutputAsString());

            $gitPullLog = $gitPullProcess->getOutputAsString();
        }

        // FILE SYNC
        $syncLog = null;
        $isFileChanged = false;
        if ($this->options->hasSync()) {
            $sync = $this->options->getSync();
            $destinations = $configure->read('destinations');
            if (! is_array($destinations)) {
                throw new RuntimeException();
            }

            foreach ($destinations as $destination) {
                $from = $destination['from'];
                $to = $destination['to'];
                $excludes = isset($destination['excludes']) ? $destination['excludes'] : [];
                $scripts = isset($destination['scripts']) ? $destination['scripts'] : [];

                $rsyncOption = $configure->read('rsync.option');
                if ($rsyncOption !== null && ! is_string($rsyncOption)) {
                    throw new RuntimeException();
                }

                $rsyncProcess = Process::define($configure->read('rsync.path', '/usr/bin/rsync'));
                $rsyncProcess
                    ->addArgument($rsyncOption)
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
                            $this->output->info($rsyncProcess->getOutputAsString());
                        } else {
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->error($rsyncProcess->getOutputAsString());
                        }

                        break;

                    case 'force':
                        $rsyncProcess
                            ->execute();
                        if ($rsyncProcess->isSuccess()) {
                            $syncLog .= $rsyncProcess->getOutputAsString();
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->info($rsyncProcess->getOutputAsString());
                        } else {
                            $isError = true;
                            $this->output->plain('$ ' . $rsyncProcess->string());
                            $this->output->error($rsyncProcess->getOutputAsString());
                        }

                        break;

                    case 'confirm':
                        $rsyncDryProcess = clone $rsyncProcess;

                        $rsyncDryProcess
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($rsyncDryProcess->isSuccess()) {
                            $this->output->plain('$ ' . $rsyncDryProcess->string());
                            $this->output->info($rsyncDryProcess->getOutputAsString());
                        } else {
                            $isError = true;
                            $this->output->plain('$ ' . $rsyncDryProcess->string());
                            $this->output->error($rsyncDryProcess->getOutputAsString());
                        }

                        echo 'Do you want to synchronize? [y/N]' . PHP_EOL;
                        $input = fgets(STDIN);
                        if (! is_string($input)) {
                            throw new RuntimeException();
                        }

                        if (trim($input) === 'y') {
                            $rsyncProcess->execute();
                            if ($rsyncProcess->isSuccess()) {
                                $syncLog .= $rsyncProcess->getOutputAsString();
                                $this->output->plain('$ ' . $rsyncProcess->string());
                                $this->output->info($rsyncProcess->getOutputAsString());
                            } else {
                                $isError = true;
                                $this->output->plain('$ ' . $rsyncProcess->string());
                                $this->output->error($rsyncProcess->getOutputAsString());
                            }
                        }

                        break;
                }

                if ($sync === 'dry' || $isError) {
                    continue;
                }

                if ($rsyncProcess->getOutput() !== null) {
                    foreach ($rsyncProcess->getOutput() as $output) {
                        if (
                            $output === '' ||
                            preg_match('/^building file/', $output) === 1 ||
                            preg_match('/^sent \d+ bytes/', $output) === 1 ||
                            preg_match('/^total size/', $output) === 1
                        ) {
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
                        $this->output->info(
                            $customScriptProcess->path() . ': ' . $customScriptProcess->getOutputAsString()
                        );
                    } else {
                        $this->output->error($customScriptProcess->getOutputAsString());
                    }
                }
            }
        }

        // FILE CHANGE NOTIFICATION
        if (! $isFileChanged) {
            return;
        }

        $slackIcon = $configure->read('slack.icon', ':sparkles:');
        if (! is_string($slackIcon)) {
            throw new RuntimeException();
        }

        $message = new SlackMessage('Deploy successful', $slackIcon);
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
                        new SlackMarkdownText('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                    )
                    ->addElement(
                        new SlackMarkdownText('Configuration: ' . $configure->getConfigPath())
                    )
            );

        $slackIncomingWebhook = $configure->read('slack.incomingWebhook');
        if (! is_string($slackIncomingWebhook)) {
            throw new RuntimeException();
        }

        $slackChannel = $configure->read('slack.channel');
        if ($slackChannel !== null && ! is_string($slackChannel)) {
            throw new RuntimeException();
        }

        $slackUsername = $configure->read('slack.username');
        if ($slackUsername !== null && ! is_string($slackUsername)) {
            throw new RuntimeException();
        }

        $slack = new Slack($slackIncomingWebhook, $slackChannel, $slackUsername, $this->http);
        $slackResult = $slack->send($message);
        if (! $slackResult->isOk()) {
            $error = $slackResult->getError();
            if (is_string($error)) {
                $this->output->error($error);
            }
        }
    }
}
