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
use Rocket\Slack\BlockKit\Block\Markdown as SlackMarkdown;
use Rocket\Slack\BlockKit\Block\Section as SlackSection;
use Rocket\Slack\BlockKit\Element\Mrkdwn as SlackMrkdwn;
use Rocket\Slack\BlockKit\Element\PlainText as SlackPlainText;
use Rocket\Slack\BlockKit\Message as SlackMessage;
use Rocket\Version;

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

        $slack = new Slack(
            $configure->read('slack.chatPostMessageUrl'),
            $configure->read('slack.appOauthToken'),
            $configure->read('slack.channel'),
            $configure->read('slack.username'),
            $this->http
        );

        $introText = '# Deploy successful';

        $message = new SlackMessage('Deploy successful', $configure->read('slack.icon', ':sparkles:'));
        $message
            ->addBlock(
                new SlackMarkdown($introText)
            )
            ->addBlock(
                (new SlackSection())->setText(
                    new SlackPlainText(get_current_user() . ' was deployed :simple_smile:')
                )
            )
            ->addBlock(
                (new SlackSection())
                    ->addField(
                        new SlackMrkdwn('*Hostname:*' . PHP_EOL . gethostname())
                    )
                    ->addField(
                        new SlackMrkdwn('*URL:*' . PHP_EOL . $configure->read('url'))
                    )
            );

        // markdown ブロックは個々のブロックではなく「1メッセージ内の text 合計」が
        // SlackMarkdown::TOTAL_TEXT_MAX_LENGTH を超えると msg_blocks_too_long エラーになる。
        // 内容を切り詰めて失うのではなく、収まらない分は新しいメッセージに分けて送信する。
        $markdownBudget = SlackMarkdown::TOTAL_TEXT_MAX_LENGTH - mb_strlen($introText, 'UTF-8');
        $fenceLength = mb_strlen('```' . PHP_EOL . PHP_EOL . '```', 'UTF-8');

        $results = [];

        // 現在組み立て中のメッセージに $length 分の余裕がなければ、いったん送信して
        // 新しいメッセージを開始する（予算をリセットする）。
        $ensureBudget = function ($length) use (&$message, &$markdownBudget, &$results, $slack, $configure) {
            if ($markdownBudget >= $length) {
                return;
            }

            $results[] = $slack->send($message);
            $message = new SlackMessage('Deploy successful (continued)', $configure->read('slack.icon', ':sparkles:'));
            // Message の $text はメッセージ本文には表示されない（通知プレビュー等でのみ使われる）ため、
            // 続きのメッセージであることが分かるように Context ブロックで本文にも明示する。
            $message->addBlock(
                (new SlackContext())->addElement(
                    new SlackMrkdwn('▶ Continued from previous message')
                )
            );
            $markdownBudget = SlackMarkdown::TOTAL_TEXT_MAX_LENGTH;
        };

        if ($gitPullLog !== null) {
            $header = '**Git pull**';
            $ensureBudget(mb_strlen($header, 'UTF-8'));
            $message
                ->addBlock(
                    new SlackDivider()
                )
                ->addBlock(
                    new SlackMarkdown($header)
                );
            $markdownBudget -= mb_strlen($header, 'UTF-8');

            $chunks = $chunker($gitPullLog, SlackMarkdown::TOTAL_TEXT_MAX_LENGTH - $fenceLength);
            foreach ($chunks as $chunk) {
                $chunkLength = $fenceLength + mb_strlen($chunk, 'UTF-8');
                $ensureBudget($chunkLength);
                $message
                    ->addBlock(
                        new SlackMarkdown('```' . PHP_EOL . $chunk . PHP_EOL . '```')
                    );
                $markdownBudget -= $chunkLength;
            }
        }

        if ($syncLog !== null) {
            $header = '**Rsync**';
            $ensureBudget(mb_strlen($header, 'UTF-8'));
            $message
                ->addBlock(
                    new SlackDivider()
                )
                ->addBlock(
                    new SlackMarkdown($header)
                );
            $markdownBudget -= mb_strlen($header, 'UTF-8');

            $chunks = $chunker($syncLog, SlackMarkdown::TOTAL_TEXT_MAX_LENGTH - $fenceLength);
            foreach ($chunks as $chunk) {
                $chunkLength = $fenceLength + mb_strlen($chunk, 'UTF-8');
                $ensureBudget($chunkLength);
                $message
                    ->addBlock(
                        new SlackMarkdown('```' . PHP_EOL . $chunk . PHP_EOL . '```')
                    );
                $markdownBudget -= $chunkLength;
            }
        }

        $message
            ->addBlock(
                new SlackDivider()
            )
            ->addBlock(
                (new SlackContext())
                    ->addElement(
                        new SlackMrkdwn('Date: ' . date("Y/m/d H:i:s"))
                    )
                    ->addElement(
                        new SlackMrkdwn('Version: ' . Main::appName() . ' ' . Version::ROCKET_VERSION)
                    )
                    ->addElement(
                        new SlackMrkdwn('Configuration: ' . $configure->getConfigPath())
                    )
            );

        $results[] = $slack->send($message);

        foreach ($results as $result) {
            if (! $result->isOk()) {
                $this->output->error($result->getError());
            }
        }
    }
}
