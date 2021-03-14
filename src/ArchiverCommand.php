<?php

namespace Rocket;

class ArchiverCommand implements ArchiverInterface
{
    /** @var string */
    private $command;

    /** @var string */
    private $working_directory_path;

    /**
     * ArchiverCommand constructor.
     *
     * @param string $command
     * @param string $working_directory_path
     */
    public function __construct($command, $working_directory_path)
    {
        $this->command = $command;
        $this->working_directory_path = $working_directory_path;
    }

    /**
     * @param string $file_path
     *
     * @return void
     */
    public function unarchive($file_path)
    {
        exec(sprintf('%s "%s" -d "%s"', $this->command, $file_path, $this->working_directory_path));
    }
}
