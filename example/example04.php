<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\logger\FileLogger;
use aphp\Parser\Bot;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$bot = new Bot( __DIR__ . '/temp');
$bot->setLogger( $logger );
$bot->settingsCSS->retryCount = 1;
$bot->settingsCSS->sleepTimeout = 1;
$bot->currentSettings->sleepTimeout = 1;

$bot->addBrowser($useragent);

// use local webserver at /tests/css-webserver
// sh tests/css-webserver/startServer.sh

$result = $bot->downloadCSSResources('http://localhost:8009/', __DIR__ . '/dw');



