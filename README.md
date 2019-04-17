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

set_time_limit(0); //  Limits the maximum execution time, unlimited

$useragent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';

$browser = new Browser($useragent, __DIR__);
$browser->navigate('https://httpstat.us/');

echo $browser->getData();
```

## Features

* Browser with cookies.
* Download files.
* Download pages with CSS.
* HTTP, HTTPS.
* Proxy.

## Syntax
### Browser class.
```php
class Browser {
	public $client = null; // HttpClient
	public $prefix = '';
	public $proxyName = '';
	protected $rawdata = null; // raw data returned by the last query
	protected $tempFileExt = null;
	protected $tempFileMime = null;

	protected $cookieFile = 'cookies.txt';
	protected $tempFile = 'download.bin';

	public function navigate ( $url );
	public function downloadImage( $url );
	public function downloadFile( $url );

	public function getData(); // null OR string
	public function getImageFileExt(); // png, jpg, gif, svg


	public function getTempFileMime(); // mime string
	public function getTempFileName();
	public function tempFileMimeIsImage();

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
Bot class used to failsafe downloading with multiple proxies and browsers.
```php
class BotSettings {
	public $retryCount = 10;
	public $sleepTimeout = 3;
	public $maxProxyCount = 75;
}

class Bot {
	public $browsers = []; // [ Browser ]
 
	public $currentSettings; // BotSettings
	public $settingsDefault; // BotSettings
	public $settingsCSS; // BotSettings

	public $tempDir;
	public $prefix = '';

	public $currentBrowser; // Browser

	public function setConfig(Config $config);

	public function add_proxy_http($proxy, $userAgent);
	public function add_proxy_socks4($proxy, $userAgent);
	public function addBrowser($userAgent);

	public function navigate ( $url );
	public function downloadImage( $url );
	public function downloadFile( $url );
	public function downloadCSSResources( $url, $resourceDir);

	public function nextProxy();
	public function runProxyTest( $url );
}
```
### Initialization with Config
```php
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
'78.40.87.18:801
78.40.87.18:802
78.40.87.18:803
78.40.87.18:804';

// adding proxies with Config
$bot->setConfig($config); 
echo 'browsers count = ' . count($bot->browsers) . PHP_EOL;
```
### Proxy Test
Used to make working proxy list.
```php
$userAgentList = new UserAgentList('textFiles/useragents.txt');

$bot->add_proxy_http('78.40.87.18:801', $userAgentList->getAgent());
$bot->add_proxy_http('78.40.87.18:802', $userAgentList->getAgent());
$bot->add_proxy_http('78.40.87.18:803', $userAgentList->getAgent());
$bot->add_proxy_http('78.40.87.18:804', $userAgentList->getAgent());

$bot->runProxyTest( 'https://httpstat.us/' );
```
### Download
Used browser to get result
```php
$result = $bot->navigate( 'https://httpstat.us/' );
if ($result) {
	echo $bot->currentBrowser->getData();
}
$result = $bot->downloadFile( 'https://httpstat.us/' ); // downloadImage
if ($result) {
	copy( $bot->currentBrowser->getTempFileName(), __DIR__ . '/dw/file.html' );
}
```
### Download page with css resources
```php
if ($bot->downloadCSSResources('https://httpstat.us/', __DIR__ . '/dw')) {
	print_r(scandir(__DIR__ . '/dw'));
	print_r(scandir(__DIR__ . '/dw/res'));
}
/*
(
    [2] => httpshttpstat.us.html
    [3] => res
)
(
    [2] => 0332350d3d76241fdc35dd0a58d4470f.css
)
*/
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