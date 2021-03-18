<?php

namespace Rocket;

use Phar;

class Main
{
    const VERSION = '0.1.5';

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
        if (! file_exists($this->options->getConfig())) {
            $this->printInit();
            return;
        }
        if ($this->options->hasVerify()) {
            if (Configure::verify($this->options->getConfig())) {
                $this->printInfo($this->options->getConfig() . ': OK');
            } else {
                $this->printError($this->options->getConfig() . ': NG');
            }
            return;
        }

        $configure = new Configure($this->options->getConfig());
        $self = $this;

        // USER CHECK
        if (posix_getpwuid(posix_geteuid())['name'] !== $configure->read('user')) {
            $this->printError('can not executed user.');
            return;
        }

        // NOTIFICATION TEST
        if ($this->options->hasNotifyTest()) {
            $slack = new Slack(
                $configure->read('slack.incomingWebhook'),
                $configure->read('slack.channel'),
                $configure->read('slack.username')
            );
            $slack->test($configure);
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
                        $self->printDebug('$ ' . $command->string());
                    }
                })
                ->execute();
            if ($command->isSuccess()) {
                $this->printInfo('> ' . $command->string());
                $this->printInfo($command->getOutputString());
            } else {
                $this->printInfo('> ' . $command->string());
                $this->printError($command->getOutputString());
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
                        $self->printDebug('$ ' . $command->string());
                    }
                })
                ->execute();

            if ($command->isSuccess()) {
                $this->printInfo('> ' . $command->string());
                $this->printInfo($command->getOutputString());
            } else {
                $this->printError('> ' . $command->string());
                $this->printError($command->getOutputString());
                return;
            }

            $git_pull_log = $command->getOutputString();
        }

        // SYNC
        $sync_log = null;
        $file_changed = false;
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
                            $self->printDebug('$ ' . $command->string());
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
                            $this->printInfo('> rsync dry');
                            $this->printInfo($command->getOutputString());
                        } else {
                            $this->printError('> rsync');
                            $this->printError($command->getOutputString());
                        }
                        break;
                    case 'force':
                        $command
                            ->execute();
                        if ($command->isSuccess()) {
                            $sync_log .= $command->getOutputString();
                            $this->printInfo('> rsync');
                            $this->printInfo($command->getOutputString());
                        } else {
                            $error = true;
                            $this->printError('> rsync');
                            $this->printError($command->getOutputString());
                        }
                        break;
                    case 'confirm':
                        $_command = clone $command;

                        $command
                            ->addArgument('--dry-run')
                            ->execute();
                        if ($command->isSuccess()) {
                            $sync_log .= $command->getOutputString();
                            $this->printInfo('> rsync dry');
                            $this->printInfo($command->getOutputString());
                        } else {
                            $error = true;
                            $this->printError('> rsync');
                            $this->printError($command->getOutputString());
                        }

                        echo "Do you want to synchronize? [y/N]\n";
                        if (trim(fgets(STDIN)) === 'y') {
                            $_command->execute();
                            if ($_command->isSuccess()) {
                                $this->printInfo('> rsync');
                                $this->printInfo($_command->getOutputString());
                            } else {
                                $error = true;
                                $this->printError('> rsync');
                                $this->printError($_command->getOutputString());
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

                    $file_changed = true;
                }

                foreach ($scripts as $script) {
                    $command = Command::define($script['path']);
                    $command
                        ->addArgument($script['option'])
                        ->setSubscribeEvent(CommandEvents::BEFORE_EXECUTION, function ($command) use ($self) {
                            /** @var Command $command */
                            if ($self->options->hasDebug()) {
                                $self->printDebug('$ ' . $command->string());
                            }
                        })
                        ->execute();

                    if ($command->isSuccess()) {
                        $this->printInfo($command->path() . ': ' . $command->getOutputString());
                    } else {
                        $this->printError($command->getOutputString());
                    }
                }
            }
        }

        // NOTIFICATION
        if ($file_changed) {
            $slackBlock = new SlackBlock();
            $slackBlock
                ->addBlock(
                    \Rocket\SlackBlock\Section::text('plain_text', get_current_user() . ' was deployed.')
                )
                ->addBlock(
                    \Rocket\SlackBlock\Section::fields()
                        ->addField(
                            new \Rocket\SlackBlock\SectionField('mrkdwn', "*Hostname:*\n" . gethostname())
                        )
                        ->addField(
                            new \Rocket\SlackBlock\SectionField('mrkdwn', "*URL:*\n" . $configure->read('url'))
                        )
                );


            if ($git_pull_log !== null) {
                $slackBlock
                    ->addBlock(
                        new SlackBlock\Divider()
                    )
                    ->addBlock(
                        \Rocket\SlackBlock\Section::text('mrkdwn', "*Git pull*\n```$git_pull_log```")
                    );
            }
            if ($sync_log !== null) {
                $slackBlock
                    ->addBlock(
                        new SlackBlock\Divider()
                    )
                    ->addBlock(
                        \Rocket\SlackBlock\Section::text('mrkdwn', "*Rsync*\n```$sync_log```")
                    );
            }

            $slackBlock
                ->addBlock(
                    new SlackBlock\Divider()
                )
                ->addBlock(
                    (new \Rocket\SlackBlock\Context())
                        ->addElement(
                            new SlackBlock\ContextElement('mrkdwn', 'Date: ' . date('Y/m/d H:i:s'))
                        )
                        ->addElement(
                            new SlackBlock\ContextElement('mrkdwn', 'Version: ' . self::appName() . ' ' . self::VERSION)
                        )
                );

            $slack = new Slack(
                $configure->read('slack.incomingWebhook'),
                $configure->read('slack.channel'),
                $configure->read('slack.username')
            );
            $slack->send($slackBlock);
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
        $working_directory_path = sys_get_temp_dir() . '/' . 'rocket-' . substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8);
        if ( ! mkdir($working_directory_path) && ! is_dir($working_directory_path)) {
            $this->printError(sprintf('Directory "%s" was not created.', $working_directory_path));
            return;
        }

//        $archiver = $this->options->hasUnzip() ?
//            new ArchiverCommand($this->options->getUnzip(), $working_directory_path) :
//            new ArchiverZip($working_directory_path);

        $updater = new Updater($working_directory_path);
        $result = $updater->upgrade();
        if ( ! $result->isOk()) {
            $this->printError($result->getError());
            return;
        }

        $this->printInfo('New version: ' . $result->getFilePath());
    }

    private function printHelp()
    {
        echo 'rocket.phar ' . self::VERSION . PHP_EOL;
        echo PHP_EOL;
        echo "Usage:\n";
        echo "  ./rocket.phar [options]\n";
        echo PHP_EOL;
        echo "Options:\n";
        echo "  -c, --config {file name}                        Configuration file name as JSON\n";
        echo "  -g, --git [pull]                                Git operation\n";
        echo "  -h, --help                                      Display this help message\n";
        echo "  -i, --init [plain|cakephp3|eccube4|wordpress]   Print sample configuration file\n";
        echo "  -n, --notify-test                               Slack notification test\n";
        echo "      --no-color                                  Without color\n";
        echo "  -s, --sync [dry|confirm|force]                  Rsync operation\n";
        echo "  -u, --upgrade                                   Download new version file\n";
        echo "      --unzip {path}                              Using zip command on upgrade\n";
        echo "  -v, --verify                                    Verify configuration file\n";
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
        $this->printWarning('Usage: php ./rocket.phar --config ./rocket.json --git [pull] --sync [dry|confirm|force]');
    }

    private function printInit()
    {
        $this->printWarning('Usage: php ./rocket.phar --init > ./rocket.json');
    }

    /**
     * @param string $path
     */
    private function printNotfoundPath($path)
    {
        $this->printError($path . ': No such file or directory');
    }

    /**
     * @param string $reason
     */
    private function printError($reason)
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
    private function printWarning($text)
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
    private function printInfo($text)
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
    private function printDebug($text)
    {
        if ($this->options->hasNoColor()) {
            echo $text . PHP_EOL;
        } else {
            echo Color::bg_text('black', 'bg-white', $text) . PHP_EOL;
        }
    }
}
