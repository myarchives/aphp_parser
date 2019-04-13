<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\logger\FileLogger;
use aphp\Parser\Bot;

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$bot = new Bot( __DIR__ . '/temp');
$bot->setLogger( $logger );

$bot->addBrowser($useragent);

// use local webserver at /tests/css-webserver

$result = $bot->downloadCSSResources('http://localhost:8009/', __DIR__ . '/dw');


