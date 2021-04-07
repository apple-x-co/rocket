<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Http;
use Rocket\OutputInterface;
use Rocket\Updater;

class UpgradeCommand implements CommandInterface
{
    /** @var OutputInterface */
    private $output;

    /** @var Http */
    private $http;

    public function __construct(OutputInterface $output, Http $http)
    {
        $this->output = $output;
        $this->http = $http;
    }

    public function execute()
    {
        $workDir = sprintf(
            '%s/rocket-%s',
            sys_get_temp_dir(),
            substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'), 0, 8)
        );
        if (! mkdir($workDir) && ! is_dir($workDir)) {
            $this->output->error(sprintf('Directory "%s" was not created.', $workDir));

            return;
        }

        $updater = new Updater($workDir, $this->http);
        $result = $updater->upgrade();
        if (! $result->isOk()) {
            $this->output->error($result->getError());

            return;
        }

        $this->output->info('New version: ' . $result->getFilePath());
    }
}
