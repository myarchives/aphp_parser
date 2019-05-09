<?php 

require __DIR__ . '/exampleApp.php';

// Note: see logs at /logs/log000.log

$app = new exampleApp();

$app->downloadProxyListIfNeeded();

$bot = $app->bot;

//$bot->runProxyTest('https://httpstat.us/'); // uncoment this ti run proxyTest

$bot->downloadFile('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($bot->currentBrowser->isDownloadSucceed()) {
	$browser = $bot->currentBrowser;
	$target = __DIR__ . '/dw/image03_1.' . $browser->getTempFile()->mimeExtension();
	copy($browser->getTempFileName(), $target );
	echo 'file downloaded ' . $target . PHP_EOL;
}

$bot->nextProxy();

$bot->downloadFile('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($bot->currentBrowser->isDownloadSucceed()) {
	$browser = $bot->currentBrowser;
	$target = __DIR__ . '/dw/image03_2.' . $browser->getTempFile()->mimeExtension();
	copy($browser->getTempFileName(), $target );
	echo 'file downloaded ' . $target . PHP_EOL;
}