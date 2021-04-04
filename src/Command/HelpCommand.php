<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Main;
use Rocket\Options;
use Rocket\OutputInterface;

class HelpCommand implements CommandInterface
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
        $rocket = Main::VERSION;

        $content = <<<EOT
rocket.phar {$rocket}

Usage:
./rocket.phar [options]

Options:
  -c, --config {file name}                        Configuration file name as JSON
  -g, --git [pull]                                Git operation
  -h, --help                                      Display this help message
  -i, --init [plain|cakephp3|eccube4|wordpress]   Print sample configuration file
  -n, --notify                                    Simple slack notification
      --notify-test                               Slack notification test
      --no-color                                  Without color
  -s, --sync [dry|confirm|force]                  Rsync operation
  -u, --upgrade                                   Download new version file
      --unzip {path}                              Using zip command on upgrade
  -v, --verify                                    Verify configuration file

EOT;

        $this->output->plain($content);
    }
}
