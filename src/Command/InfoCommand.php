<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Main;
use Rocket\Options;
use Rocket\OutputInterface;

class InfoCommand implements CommandInterface
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
        $uname = php_uname();
        $binary = PHP_BINARY;
        $version = PHP_VERSION;
        $ini = php_ini_loaded_file();
        $rocket = MAIN::VERSION;

        $content = <<<EOT
OS:             {$uname}
PHP binary:     {$binary}
PHP version:    {$version}
php.ini used:   {$ini}
rocket version: {$rocket}

EOT;

        $this->output->plain($content);
    }
}
