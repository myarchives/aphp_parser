<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\Parser\Browser;
use aphp\logger\FileLogger;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$browser = new Browser($useragent, __DIR__);
$browser->setLogger($logger);


$browser->downloadImage('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($browser->isDownloadSucceed()) {
	copy($browser->getTempFileName(), __DIR__ . '/image' . $browser->getImageFileExt());
}

$browser->downloadFile('https://www.lifeonnetwork.com/wp-content/uploads/2017/11/download.png');
if ($browser->isDownloadSucceed()) {
	copy($browser->getTempFileName(), __DIR__ . '/filename.png');
}