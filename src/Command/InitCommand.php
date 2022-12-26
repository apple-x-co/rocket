<?php

namespace Rocket\Command;

use Rocket\CommandInterface;
use Rocket\Options;
use Rocket\OutputInterface;
use RuntimeException;

class InitCommand implements CommandInterface
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
        $templateName = $this->options->getInit();

        $content = null;
        if ($templateName === 'cakephp3') {
            $cakephp3 = file_get_contents(__DIR__ . '/../config/cakephp3.json');
            if (is_string($cakephp3)) {
                $content = $cakephp3;
            }
        }

        if ($templateName === 'eccube4') {
            $eccube4 = file_get_contents(__DIR__ . '/../config/eccube4.json');
            if (is_string($eccube4)) {
                $content = $eccube4;
            }
        }

        if ($templateName === 'wordpress') {
            $wordpress = file_get_contents(__DIR__ . '/../config/wordpress.json');
            if (is_string($wordpress)) {
                $content = $wordpress;
            }
        }

        if ($content === null) {
            $plain = file_get_contents(__DIR__ . '/../config/plain.json');
            if (is_string($plain)) {
                $content = $plain;
            }
        }

        if (! is_string($content)) {
            throw new RuntimeException();
        }

        $this->output->plain($content);
    }
}
