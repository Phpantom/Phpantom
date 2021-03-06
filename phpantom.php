<?php


foreach (array(__DIR__ . '/../../autoload.php', __DIR__ . '/../vendor/autoload.php', __DIR__ . '/vendor/autoload.php') as $file) {
    if (file_exists($file)) {
        define('PHPANTOM_COMPOSER_INSTALL', $file);
        break;
    }
}

unset($file);

if (!defined('PHPANTOM_COMPOSER_INSTALL')) {
    fwrite(STDERR,
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'wget http://getcomposer.org/composer.phar' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
    die(1);
}

require PHPANTOM_COMPOSER_INSTALL;

use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new \Phpantom\Command\CrawlCommand());
$application->add(new \Phpantom\Command\ProcessCommand());
$application->add(new \Phpantom\Command\InfoCommand());
$application->run();
