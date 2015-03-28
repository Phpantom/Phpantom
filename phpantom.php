<?php

require __DIR__.'/vendor/autoload.php';

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new \Phpantom\Command\CrawlCommand());
$application->add(new \Phpantom\Command\ProcessCommand());
$application->add(new \Phpantom\Command\InfoCommand());
$application->run();
