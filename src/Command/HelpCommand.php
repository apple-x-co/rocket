<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\OutputInterface;
use Rocket\Version;

class HelpCommand implements CommandInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $rocket = Version::ROCKET_VERSION;

        $content = <<<EOT
rocket.phar {$rocket}

Usage:
./rocket.phar [options]

Options:
  -c, --config {file name}                       Configuration file name as JSON
  -g, --git [pull]                               Git operation
  -h, --help                                     Display this help message
  -i, --init [plain|cakephp3|eccube4|wordpress]  Print sample configuration file
  -n, --notify                                   Simple slack notification
      --notify-test                              Slack notification test
      --no-color                                 Without color
  -s, --sync [dry|confirm|force]                 Rsync operation
      --ssl [TLSv1_0|TLSv1_1|TLSv1_2|TLSv1_3]    SSL Version
  -u, --upgrade                                  Download new version file
      --unzip {path}                             Using zip command on upgrade
  -v, --verify                                   Verify configuration file

EOT;

        $this->output->plain($content);
    }
}
