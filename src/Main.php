<?php

namespace Rocket;

use Phar;
use Rocket\Command\DeployCommand;
use Rocket\Command\HelpCommand;
use Rocket\Command\InfoCommand;
use Rocket\Command\InitCommand;
use Rocket\Command\SlackNotificationCommand;
use Rocket\Command\SlackNotificationTestCommand;
use Rocket\Command\UpgradeCommand;
use Rocket\Command\UsageCommand;
use Rocket\Command\VerifyCommand;
use RuntimeException;

class Main
{
    /** @var Options */
    private $options = null;

    /**
     * @param Options $options
     */
    public function __construct($options)
    {
        $this->options = $options;
    }

    /**
     * @return void
     */
    public function run()
    {
        $output = $this->options->hasNoColor() ? new Output() : new ColorOutput();
        $http = (new Http($this->options->hasTls() ? $this->options->getTls() : null));

        $command = null;

        if ($this->options->hasInfo()) {
            $command = new InfoCommand($output);
        }

        if ($this->options->hasHelp()) {
            $command = new HelpCommand($output);
        }

        if ($this->options->hasInit()) {
            $command = new InitCommand($this->options, $output);
        }

        if ($this->options->hasUpgrade()) {
            $command = new UpgradeCommand($output, $http);
        }

        if ($this->options->hasConfig()) {
            $configPath = realpath($this->options->getConfig());
            if (! is_string($configPath)) {
                throw new RuntimeException();
            }

            if (file_exists($configPath)) {
                if ($this->options->hasVerify()) {
                    $command = new VerifyCommand($this->options, $output);
                }

                if ($this->options->hasNotify()) {
                    $command = new SlackNotificationCommand($this->options, $http);
                }

                if ($this->options->hasNotifyTest()) {
                    $command = new SlackNotificationTestCommand($this->options, $http);
                }

                if ($command === null) {
                    $command = new DeployCommand($this->options, $output, $http);
                }
            }
        }

        if ($command === null) {
            $command = new UsageCommand($output);
        }

        $command->execute();
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
}
