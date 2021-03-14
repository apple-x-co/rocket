<?php

namespace Rocket;

use ZipArchive;

class ArchiverZip implements ArchiverInterface
{
    /** @var string */
    private $working_directory_path;

    /**
     * ArchiverZip constructor.
     *
     * @param string $working_directory_path
     */
    public function __construct($working_directory_path)
    {
        $this->working_directory_path = $working_directory_path;
    }

    /**
     * @param string $file_path
     *
     * @return void
     */
    public function unarchive($file_path)
    {
        $zip = new ZipArchive();
        $result = $zip->open($file_path);
        if ($result !== true) {
            echo "open zip file failure.\n";
            $zip->close();
            return;
        }

        $zip->extractTo($this->working_directory_path);
        $zip->close();
    }
}
