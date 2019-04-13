<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\logger\FileLogger;
use aphp\Parser\Bot;
use aphp\Parser\Config;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$config = new Config();

$bot = new Bot( __DIR__ . '/temp');
$bot->setLogger( $logger );

$config->proxyURLs_text = 
'207.154.231.211:8080
118.174.220.133:50616
177.85.200.241:58412
124.200.105.154:8118
203.210.84.145:8080
142.93.58.206:3128
190.122.150.97:8080
88.198.24.108:3128
1.20.103.135:44662
103.216.59.122:38102
167.99.1.100:3128
194.29.60.48:45416
1.2.169.44:48545';

// adding proxies with config
$bot->setConfig($config); 
echo 'browsers count = ' . count($bot->browsers) . PHP_EOL;

if ($bot->downloadCSSResources('https://httpstat.us/', __DIR__ . '/dw')) {
//if ($bot->downloadCSSResources('http://proxy-daily.com/?PageSpeed=noscript', __DIR__ . '/dw')) {
//if ($bot->downloadCSSResources('https://www.php.net/downloads.php', __DIR__ . '/dw')) {
	print_r(scandir(__DIR__ . '/dw'));
	print_r(scandir(__DIR__ . '/dw/res'));
} else {
	echo 'downloadCSSResources F';
}