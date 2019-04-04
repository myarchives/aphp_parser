# Parser

![PHP Support](https://img.shields.io/badge/php%20tested-5.6-brightgreen.svg)
![PHP Support](https://img.shields.io/badge/php%20tested-7.1-brightgreen.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Travis](https://api.travis-ci.org/GonistLelatel/aphp_parser.svg?branch=master)

## Introduction

`Parser` is a basic curl wrapper for PHP.<br>
See [php.net/curl](https://www.php.net/curl) for more information about the libcurl extension for PHP.

## Installation
PHP5.6 , PHP7.0+

`composer require aphp/parser`

## Hello world

```php
require 'vendor/autoload.php';

use aphp\Parser\Browser;
use aphp\logger\FileLogger;

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$logger = FileLogger::getInstance();
$logger->configure(__DIR__ . '/logs/log');
$logger->startLog();

$browser = new Browser($useragent, __DIR__);
$browser->setLogger($logger);

$browser->navigate('https://httpstat.us/');

echo $browser->getData();
```

## Features

* Browser with cookies.
* Download files.
* HTTP, HTTPS.
* Proxy.

## Syntax
### Browser class.
```php
class Browser {
	public $client = null; // HttpClient

	public function navigate ( $url );
	public function downloadImage( $url );
	public function downloadFile( $url );

	public function getData(); // null OR string
	public function getImageFileExt(); // png, jpg, gif, svg

	public function getTempFileMime(); // mime string
	public function getTempFileName();

	public function isNavigateSucceed();
	public function isDownloadSucceed();
}
```
### Initialization
```php
set_time_limit(0); //  Limits the maximum execution time, unlimited

$userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$browser = new Browser( $userAgent, 'dirForCookiesAndFiles' );
$browser->setLogger( $logger ); // optional
```
### GET Text
```php
$browser->navigate('https://httpstat.us/');
echo $browser->getData();
```
### Error detection
```php
if ($browser->navigate('https://httpstat.us/')) {
	echo $browser->getData();
}

$browser->navigate('https://httpstat.us/');
if ($this->isNavigateSucceed()) {
	echo $browser->getData();
}
```
### Image Download
```php
$browser->downloadImage('http://www.djswebdesign.com/wp-content/uploads/2012/05/PHP-MySQL.png');
if ($browser->isDownloadSucceed()) {
	copy($browser->getTempFileName(), __DIR__ . '/image' . $browser->getImageFileExt());
}
```
### File Download
```php
$browser->downloadFile('https://www.lifeonnetwork.com/wp-content/uploads/2017/11/download.png');
if ($browser->isDownloadSucceed()) {
	copy($browser->getTempFileName(), __DIR__ . '/filename.png');
}
```
## Bot
### Bot class used to failsafe downloading with multiple proxies and browsers.
```php
class Bot {
	public $retryCount = 5;
	public $sleepTimeout = 3;
	public $browsers = []; // [ Browser ]

	public $currentBrowser; // Browser

	public function add_proxy_http($proxy, $userAgent);
	public function add_proxy_socks4($proxy, $userAgent);

	public function navigate ( $url ); 
	public function downloadImage( $url );
	public function downloadFile( $url );

	public function nextProxy();

	public function runProxyTest( $url ); 
}
```
### Proxy Test
Used to make working proxy list.
```php
use aphp\Parser\Bot;

$bot = new Bot(__DIR__ . '/temp'  );

$userAgent[0] = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1';
$userAgent[1] = 'Mozilla/5.0 (Windows NT 5.1; rv:33.0) Gecko/20100101 Firefox/33.0';
$userAgent[2] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';
$userAgent[3] = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36';

$bot->add_proxy_http('78.40.87.18:801', $userAgent[0]);
$bot->add_proxy_http('78.40.87.18:802', $userAgent[1]);
$bot->add_proxy_http('78.40.87.18:803', $userAgent[2]);
$bot->add_proxy_http('78.40.87.18:804', $userAgent[3]);

$bot->runProxyTest( 'https://httpstat.us/' );

print_r($bot->browsers);
```
### Downloading
Used browser to get result
```php
$result = $bot->navigate( 'https://httpstat.us/' );
if ($result) {
	echo $bot->currentBrowser->getData();
}
```
### Manual proxy switcher
```php
$bot->nextProxy();
```
## More features
For more features:
* Read source of [HttpClient](src/HttpClient.php) class
* Read [CURL](https://www.php.net/curl) documentation
* Read source code and examples
* Practice with `Parser` in real code