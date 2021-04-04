<?php

namespace Rocket;

interface CommandInterface
{
    public function __construct(Options $options, OutputInterface $output);

    /**
     * @return void
     */
    public function execute();
}
