<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Options;
use Rocket\OutputInterface;
use Rocket\Updater;

class UpgradeCommand implements CommandInterface
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
        $working_directory_path = sys_get_temp_dir() . '/' . 'rocket-' . substr(str_shuffle('1234567890abcdefghijklmnopqrstuvwxyz'),
                0, 8);
        if (! mkdir($working_directory_path) && ! is_dir($working_directory_path)) {
            $this->output->error(sprintf('Directory "%s" was not created.', $working_directory_path));

            return;
        }

        $updater = new Updater($working_directory_path);
        $result = $updater->upgrade();
        if (! $result->isOk()) {
            $this->output->error($result->getError());

            return;
        }

        $this->output->info('New version: ' . $result->getFilePath());
    }
}
