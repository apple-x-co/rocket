<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\OutputInterface;
use Rocket\Version;

class InfoCommand implements CommandInterface
{
    /** @var OutputInterface */
    private $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function execute()
    {
        $uname = php_uname();
        $binary = PHP_BINARY;
        $version = PHP_VERSION;
        $ini = php_ini_loaded_file();
        $rocket = Version::ROCKET_VERSION;

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
