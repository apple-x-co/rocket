<?php

namespace Rocket;

class Main
{
    const VERSION = '0.1.4';

    /** @var Options */
    private $options = null;

    /**
     * Main constructor.
     *
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
                            new SlackBlock\ContextElement('mrkdwn', 'Version: ' . self::VERSION)
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
     *
     */
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

    /**
     *
     */
    private function printHelp()
    {
        echo 'rocket.phar ' . self::VERSION . "\n";
        echo "\n";
        echo "Usage:\n";
        echo "  php ./rocket.phar [options]\n";
        echo "\n";
        echo "Options:\n";
        echo "  -h, --help                                      show this help message and exit\n";
        echo "  -c, --config {file name}                        configure file name\n";
        echo "  -g, --git [pull]                                git  \n";
        echo "  -s, --sync [dry|confirm|force]                  rsync\n";
        echo "  -i, --init [plain|cakephp3|eccube4|wordpress]   print default configure file\n";
        echo "  -n, --notify-test                               notification test\n";
        echo "  -u, --upgrade                                   upgrade\n";
        echo "      --unzip {path}                              using zip command on upgrade\n";
        echo "  -v, --verify                                    verify configure\n";
    }

    /**
     * @var string $target
     */
    private function printConfig($template)
    {
        if ($template === 'cakephp3') {
            echo file_get_contents(__DIR__ . '/config/cakephp3.json');
            echo "\n";
            return;
        }
        if ($template === 'eccube4') {
            echo file_get_contents(__DIR__ . '/config/eccube4.json');
            echo "\n";
            return;
        }
        if ($template === 'wordpress') {
            echo file_get_contents(__DIR__ . '/config/wordpress.json');
            echo "\n";
            return;
        }

        echo file_get_contents(__DIR__ . '/config/plain.json');
        echo "\n";
    }

    /**
     *
     */
    private function printUsage()
    {
        $this->printWarning('usage: php ./rocket.phar --config ./rocket.json --git [pull] --sync [dry|confirm|force]');
    }

    /**
     *
     */
    private function printInit()
    {
        $this->printWarning('usage: php ./rocket.phar --init > ./rocket.json');
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
        echo Color::text('red', 'rocket.phar: ' . $reason);
        echo "\n";
    }

    /**
     * @param $text
     */
    private function printWarning($text)
    {
        echo Color::text('purple', $text);
        echo "\n";
    }

    /**
     * @param $text
     */
    private function printInfo($text)
    {
        echo Color::text('cyan', $text);
        echo "\n";
    }

    /**
     * @param $text
     */
    private function printDebug($text)
    {
        echo Color::bg_text('black', 'bg-white', $text);
        echo "\n";
    }
}
