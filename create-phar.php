<?php
$srcRoot = __DIR__ . "/src";
$buildRoot = __DIR__ . "/build";
$vendor = __DIR__ . "/vendor";

$phar = new Phar(
    $buildRoot . "/phpantom.phar",
    FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME,
    "myapp.phar"
);
$phar->addFile('index.php');
$phar->addFile('phpantom.php');
$phar->setDefaultStub('index.php', 'index.php');
$phar->buildFromDirectory($srcRoot, '/.php$/');
$phar->buildFromDirectory($vendor);


//$phar->setStub($phar->createDefaultStub("index.php"));
