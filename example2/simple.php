<?php
require __DIR__ .'/../vendor/autoload.php';
use aphp\Parser\Bot;
use aphp\Parser\BotSettings;
use aphp\Logger\FileLogger;
use aphp\HttpClient\BrowserConfig;

@mkdir( __DIR__ . '/logs');
@mkdir( __DIR__ . '/temp');

$logger = new FileLogger();
$logger->configure( __DIR__ . '/logs/log' );
$logger->startLog();

$settings = new BotSettings();
$settings->browserConfig = new BrowserConfig( __DIR__ . '/temp' );
$settings->proxyList = [ '' ]; // usage without proxies, with local machine ip

$bot = new Bot( $settings );
$bot->setLogger( $logger );

$r = $bot->runTaskNoChangeProxy(function() use($bot){
	return $bot->navigate('https://httpstat.us');
});
if ($r) {
	echo $bot->getData();
} else {
	echo 'failed';
}