<?php

namespace Rocket;

class Chunker
{
    /**
     * @param string $text
     * @param int $length
     *
     * @return array<string>
     */
    public function __invoke($text, $length)
    {
        $chunks = [];
        $buf = null;

        $lines = explode(PHP_EOL, $text);
        foreach ($lines as $line) {
            if (strlen($line) >= $length) {
                foreach (str_split($line, $length) as $chunk) {
                    $chunks[] = $chunk;
                }

                continue;
            }

            $line .= PHP_EOL;

            if (strlen($buf . $line) > $length) {
                $chunks[] = $buf;
                $buf = null;
            }

            $buf .= $line;
        }

        if ($buf !== null) {
            $chunks[] = $buf;
        }

        return $chunks;
    }
}
