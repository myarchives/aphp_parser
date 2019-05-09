<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\Parser\Browser;
use aphp\logger\FileLogger;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$tempDir = __DIR__ . '/temp';

$browser = new Browser($useragent, $tempDir);
$browser->setLogger($logger);

$browser->downloadFile('http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/editor/icons3.png');
if ($browser->isDownloadSucceed()) {
	$target = __DIR__ . '/dw/filename.' . $browser->getTempFile()->mimeExtension();
	copy( $browser->getTempFileName(), $target );
	echo 'file downloaded ' . $target . PHP_EOL;
}