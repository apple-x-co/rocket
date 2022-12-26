<?php

namespace Rocket;

class Chunker
{
    /**
     * @param string $text
     * @param int    $chunkSize
     *
     * @return array<string>
     */
    public function __invoke($text, $chunkSize)
    {
        $chunks = mb_str_split($text, $chunkSize);

        return is_array($chunks) ? $chunks : [];
    }
}
