<?php

namespace Rocket;

use Phar;
use Rocket\Slack\BlockKit\Block\Context as SlackContext;
use Rocket\Slack\BlockKit\Block\Divider as SlackDivider;
use Rocket\Slack\BlockKit\Block\Header as SlackHeader;
use Rocket\Slack\BlockKit\Block\Section as SlackSection;
use Rocket\Slack\BlockKit\Element\MarkdownText as SlackMarkdownText;
use Rocket\Slack\BlockKit\Element\PlainText as SlackPlainText;
use Rocket\Slack\BlockKit\Message;
use Rocket\Slack\BlockKit\Message as SlackMessage;

class Main
{
    const VERSION = '0.1.8';

    /** @var Options */
    private $options = null;

    /**
     * @param Options $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    public function run()
    {
        // INIT
        if ($this->options->hasInit()) {
            $this->printConfig($this->options->getInit());

            return;
        }

        // INFO
        if ($this->options->hasInfo()) {
            $this->printInfo();

            return;
        }

        // HELP
        if ($this->options->hasHelp()) {
            $this->printHelp();

            return;
        }

        // UPGRADE
        if ($this->options->hasUpgrade()) {
            $this->upgrade();

            return;
        }

        // CONFIGURE
        if (! $this->options->hasConfig()) {
            $this->printUsage();

            return;
        }

        $config_path = realpath($this->options->getConfig());
        if (! file_exists($config_path)) {
            $this->printInit();

            return;
        }

        if ($this->options->hasVerify()) {
            if (Configure::verify($config_path)) {
                $this->info($config_path . ': OK');
            } else {
                $this->error($config_path . ': NG');
            }

            return;
        }

        $configure = new Configure($config_path);
        $self = $this;

        // NOTIFICATION
        if ($this->options->hasNotify()) {
            $lines = [];
            while ($line = fgets(STDIN)) {
                $lines[] = trim($line);
            }

            $message = new Message();

            $chunks = str_split(implode(PHP_EOL, $lines), SlackSection::TEXT_MAX_LENGTH - 6);
            foreach ($chunks as $chunk) {
                $message
                    ->addBlock(
                        (new SlackSection())
                            ->setText(
                                new SlackMarkdownText('```' . $chunk . '```')
                            )
                    );
            }

            $slack = new Slack(
                $configure->read('slack.incomingWebhook'),
                $configure->read('slack.channel'),
                $configure->read('slack.username')
            );
            $slack->send($message);

            return;
        }

        // USER CHECK
        if (posix_getpwuid(posix_geteuid())['name'] !== $configure->read('user')) {
            $this->error('can not executed user.');

            return;
        }

        // NOTIFICATION TEST
        if ($this->options->hasNotifyTest()) {
            $slack = new Slack(
                $configure->read('slack.incomingWebhook'),
                $configure->read('slack.channel'),
                $configure->read('slack.username')
            );
            $slackResult = $slack->test($configure);
            if (! $slackResult->isOk()) {
                $this->error($slackResult->getError());
            }

            return;
        }

        // GIT
        $git_pull_log = null;
        if ($this->options->getGit() === 'pull') {
            $directory_path = $configure->read('source.directory');
            if (! is_dir($directory_path)) {
                $this->printNotfoundPath($directory_path);

                return;
            }

            // CHECK
            $command = Command::define($configure->read('git.path', '/usr/bin/git'));
            $command
                ->addArgument('--git-dir', $directory_path . '.git', '=')
                ->addArgument('--work-tree', $directory_path, '=')
                ->addArgument('remote')
                ->addArgument('show')
                ->addArgument('origin')
                ->setSubscribeEvent(CommandEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                    /** @var Command $command */
                    if ($self->options->hasDebug()) {
                        $self->debug('$ ' . $command->string());
                    }
                })
                ->execute();
            if ($command->isSuccess()) {
                $this->info('> ' . $command->string());
                $this->info($command->getOutputString());
            } else {
                $this->info('> ' . $command->string());
                $this->error($command->getOutputString());

                return;
            }

            if (strpos($command->getOutputString(), 'local out of date') === false) {
                return;
            }

            // PULL WITH PRUNE
            $command = Command::define($configure->read('git.path', '/usr/bin/git'));
            $command
                ->addArgument('--git-dir', $directory_path . '.git', '=')
                ->addArgument('--work-tree', $directory_path, '=')
                ->addArgument('pull')
                ->addArgument('--prune')
                ->setSubscribeEvent(CommandEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                    /** @var Command $command */
                    if ($self->options->hasDebug()) {
                        $self->debug('$ ' . $command->string());
                    }
                })
                ->execute();

            if ($command->isSuccess()) {
                $this->info('> ' . $command->string());
                $this->info($command->getOutputString());
            } else {
                $this->error('> ' . $command->string());
                $this->error($command->getOutputString());

                return;
            }

            $git_pull_log = $command->getOutputString();
        }

        // SYNC
        $sync_log = null;
        $is_file_changed = false;
        if ($this->options->hasSync()) {
            $sync = $this->options->getSync();
            $destinations = $configure->read('destinations');
            foreach ($destinations as $destination) {
                $from = $destination['from'];
                $to = $destination['to'];
                $excludes = isset($destination['excludes']) ? $destination['excludes'] : [];
                $scripts = isset($destination['scripts']) ? $destination['scripts'] : [];

                $command = Command::define($configure->read('rsync.path', '/usr/bin/rsync'));
                $command
                    ->addArgument($configure->read('rsync.option'))
                    ->addArgument($from)
                    ->addArgument($to)
                    ->setSubscribeEvent(CommandEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                        /** @var Command $command */
                        if ($self->options->hasDebug()) {
                            $self->debug('$ ' . $command->string());
                        }
                    });

                foreach ($excludes as $exclude) {
                    $command->addArgument('--exclude', $exclude, '=');
                }

                $error = false;

                switch ($sync) {
                    case 'dry':
                        $command
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($command->isSuccess()) {
                            $this->info('> rsync dry');
                            $this->info($command->getOutputString());
                        } else {
                            $this->error('> rsync');
                            $this->error($command->getOutputString());
                        }

                        break;
                    case 'force':
                        $command
                            ->execute();
                        if ($command->isSuccess()) {
                            $sync_log .= $command->getOutputString();
                            $this->info('> rsync');
                            $this->info($command->getOutputString());
                        } else {
                            $error = true;
                            $this->error('> rsync');
                            $this->error($command->getOutputString());
                        }

                        break;
                    case 'confirm':
                        $_command = clone $command;

                        $command
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($command->isSuccess()) {
                            //$sync_log .= $command->getOutputString();
                            $this->info('> rsync dry');
                            $this->info($command->getOutputString());
                        } else {
                            $error = true;
                            $this->error('> rsync');
                            $this->error($command->getOutputString());
                        }

                        echo 'Do you want to synchronize? [y/N]' . PHP_EOL;
                        if (trim(fgets(STDIN)) === 'y') {
                            $_command->execute();
                            if ($_command->isSuccess()) {
                                $sync_log .= $_command->getOutputString();
                                $this->info('> rsync');
                                $this->info($_command->getOutputString());
                            } else {
                                $error = true;
                                $this->error('> rsync');
                                $this->error($_command->getOutputString());
                            }
                        }

                        break;
                }

                if ($sync === 'dry' || $error) {
                    continue;
                }

                foreach ($command->getOutput() as $output) {
                    if (preg_match('/^building file/', $output) === 1 ||
                        preg_match('/^sent \d+ bytes/', $output) === 1 ||
                        preg_match('/^total size/', $output) === 1 ||
                        $output === '') {
                        continue;
                    }

                    $is_file_changed = true;
                }

                foreach ($scripts as $script) {
                    $command = Command::define($script['path']);
                    $command
                        ->addArgument($script['option'])
                        ->setSubscribeEvent(CommandEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                            /** @var Command $command */
                            if ($self->options->hasDebug()) {
                                $self->debug('$ ' . $command->string());
                            }
                        })
                        ->execute();

                    if ($command->isSuccess()) {
                        $this->info($command->path() . ': ' . $command->getOutputString());
                    } else {
                        $this->error($command->getOutputString());
                    }
                }
            }
        }

        // NOTIFICATION
        if ($is_file_changed) {
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

            if ($git_pull_log !== null) {
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

                $chunks = str_split($git_pull_log, SlackSection::TEXT_MAX_LENGTH - 6);
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

            if ($sync_log !== null) {
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

                $chunks = str_split($sync_log, SlackSection::TEXT_MAX_LENGTH - 6);
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
                            new SlackMarkdownText('Version: ' . self::appName() . ' ' . self::VERSION)
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
                $this->error($slackResult->getError());
            }
        }
    }

    /**
     * @return string
     */
    public static function appName()
    {
        $name = Phar::running(false);
        if ($name === '') {
            $name = __FILE__;
        }

        return $name;
    }

    private function upgrade()
    {
        $working_directory_path = sys_get_temp_dir() . '/' . 'rocket-' . substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'),
                0, 8);
        if (! mkdir($working_directory_path) && ! is_dir($working_directory_path)) {
            $this->error(sprintf('Directory "%s" was not created.', $working_directory_path));

            return;
        }

//        $archiver = $this->options->hasUnzip() ?
//            new ArchiverCommand($this->options->getUnzip(), $working_directory_path) :
//            new ArchiverZip($working_directory_path);

        $updater = new Updater($working_directory_path);
        $result = $updater->upgrade();
        if (! $result->isOk()) {
            $this->error($result->getError());

            return;
        }

        $this->info('New version: ' . $result->getFilePath());
    }

    private function printInfo()
    {
        echo 'OS:             ' . php_uname() . PHP_EOL;
        echo 'PHP binary:     ' . PHP_BINARY . PHP_EOL;
        echo 'PHP version:    ' . PHP_VERSION . PHP_EOL;
        echo 'php.ini used:	' . php_ini_loaded_file() . PHP_EOL;
        echo 'rocket version: ' . self::VERSION . PHP_EOL;
    }

    private function printHelp()
    {
        echo 'rocket.phar ' . self::VERSION . PHP_EOL;
        echo PHP_EOL;
        echo 'Usage:' . PHP_EOL;
        echo './rocket.phar [options]' . PHP_EOL;
        echo PHP_EOL;
        echo 'Options:' . PHP_EOL;
        echo '  -c, --config {file name}                        Configuration file name as JSON' . PHP_EOL;
        echo '  -g, --git [pull]                                Git operation' . PHP_EOL;
        echo '  -h, --help                                      Display this help message' . PHP_EOL;
        echo '  -i, --init [plain|cakephp3|eccube4|wordpress]   Print sample configuration file' . PHP_EOL;
        echo '  -n, --notify                                    Simple slack notification' . PHP_EOL;
        echo '      --notify-test                               Slack notification test' . PHP_EOL;
        echo '      --no-color                                  Without color' . PHP_EOL;
        echo '  -s, --sync [dry|confirm|force]                  Rsync operation' . PHP_EOL;
        echo '  -u, --upgrade                                   Download new version file' . PHP_EOL;
        echo '      --unzip {path}                              Using zip command on upgrade' . PHP_EOL;
        echo '  -v, --verify                                    Verify configuration file' . PHP_EOL;
    }

    /**
     * @var string $template
     */
    private function printConfig($template)
    {
        if ($template === 'cakephp3') {
            echo file_get_contents(__DIR__ . '/config/cakephp3.json') . PHP_EOL;

            return;
        }

        if ($template === 'eccube4') {
            echo file_get_contents(__DIR__ . '/config/eccube4.json') . PHP_EOL;

            return;
        }

        if ($template === 'wordpress') {
            echo file_get_contents(__DIR__ . '/config/wordpress.json') . PHP_EOL;

            return;
        }

        echo file_get_contents(__DIR__ . '/config/plain.json') . PHP_EOL;
    }

    private function printUsage()
    {
        $this->warning('Usage: ./rocket.phar --config ./rocket.json --git [pull] --sync [dry|confirm|force]');
    }

    private function printInit()
    {
        $this->warning('Usage: ./rocket.phar --init > ./rocket.json');
    }

    /**
     * @param string $path
     */
    private function printNotfoundPath($path)
    {
        $this->error($path . ': No such file or directory');
    }

    /**
     * @param string $reason
     */
    private function error($reason)
    {
        $string = self::appName() . ': ' . $reason;
        if ($this->options->hasNoColor()) {
            echo $string . PHP_EOL;
        } else {
            echo Color::text('red', $string) . PHP_EOL;
        }
    }

    /**
     * @param string $text
     */
    private function warning($text)
    {
        if ($this->options->hasNoColor()) {
            echo $text . PHP_EOL;
        } else {
            echo Color::text('purple', $text) . PHP_EOL;
        }
    }

    /**
     * @param string $text
     */
    private function info($text)
    {
        if ($this->options->hasNoColor()) {
            echo $text . PHP_EOL;
        } else {
            echo Color::text('cyan', $text) . PHP_EOL;
        }
    }

    /**
     * @param string $text
     */
    private function debug($text)
    {
        if ($this->options->hasNoColor()) {
            echo $text . PHP_EOL;
        } else {
            echo Color::bg_text('black', 'bg-white', $text) . PHP_EOL;
        }
    }
}
