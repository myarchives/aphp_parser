<?php 

require __DIR__ . '/../vendor/autoload.php';

use aphp\Parser\Browser;
use aphp\logger\FileLogger;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$browser = new Browser($useragent, __DIR__ . '/temp');
$browser->setLogger($logger);

// proxy

// https://hidemyna.me/en/proxy-list/

//$browser->client->set_proxy_http('78.40.87.18:808');
//$browser->client->set_proxy_socks4('54.38.110.35:48034');

//$browser->navigate('https://httpstat.us/');
$browser->navigate('https://jsonplaceholder.typicode.com/users');

echo $browser->getData();