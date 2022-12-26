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
            $content = (string)file_get_contents(__DIR__ . '/../config/cakephp3.json');
        }

        if ($templateName === 'eccube4') {
            $content = (string)file_get_contents(__DIR__ . '/../config/eccube4.json');
        }

        if ($templateName === 'wordpress') {
            $content = (string)file_get_contents(__DIR__ . '/../config/wordpress.json');
        }

        if ($content === null) {
            $content = (string)file_get_contents(__DIR__ . '/../config/plain.json');
        }

        $this->output->plain($content);
    }
}
