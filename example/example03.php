<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\logger\FileLogger;
use aphp\Parser\Bot;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$bot = new Bot( __DIR__ );
$bot->setLogger( $logger );

// https://hidemyna.me/en/proxy-list/

$bot->add_proxy_http('94.242.58.142:655', $useragent);
$bot->add_proxy_http('94.242.55.108:655', $useragent);
$bot->add_proxy_http('94.242.58.14:655', $useragent);
$bot->add_proxy_http('91.221.109.138:3128', $useragent);
$bot->add_proxy_http('91.221.109.136:3128', $useragent);
$bot->add_proxy_http('84.201.254.47:3128', $useragent);

$bot->runProxyTest('https://httpstat.us/');

$tempDir = __DIR__ . '/temp';

@unlink($tempDir . '/image.png');
@unlink($tempDir . '/image2.png');

$bot->downloadImage('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($bot->currentBrowser->isDownloadSucceed()) {
	$browser = $bot->currentBrowser;
	copy($browser->getTempFileName(), $tempDir . '/image' . $browser->getImageFileExt());
}

$bot->nextProxy();

$bot->downloadImage('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($bot->currentBrowser->isDownloadSucceed()) {
	$browser = $bot->currentBrowser;
	copy($browser->getTempFileName(), $tempDir . '/image2' . $browser->getImageFileExt());
}