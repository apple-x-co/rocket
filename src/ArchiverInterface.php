<?php

namespace Rocket;

interface ArchiverInterface
{
    /**
     * @param string $file_path
     *
     * @return void
     */
    public function unarchive($file_path);
}
