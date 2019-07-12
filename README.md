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
* Example App.

## Syntax
### Browser class.
```php
class Browser {
	public $client = null; // HttpClient
	public $prefix = '';
	public $proxyName = '';
	protected $rawdata = null; // raw data returned by the last query

	protected $cookieFile = 'cookies.txt';
	protected $tempFile = null; // aphp\Files\File
	protected $tempFileDownloaded = false;
	// bool
	public function navigate ( $url );
	public function downloadFile( $url );

	public function getData(); // null OR string

	// aphp\Files\File
	public function getTempFile();
	// aphp\Files\FilePath
	public function getTempFilePath();
	// string
	public function getTempFileName();
	// bool
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
### File Download
```php
$browser->downloadFile('https://www.lifeonnetwork.com/wp-content/uploads/2017/11/download.png');
if ($browser->isDownloadSucceed()) {
	$target = __DIR__ . '/dw/filename.' . $browser->getTempFile()->mimeExtension();
	copy($browser->getTempFileName(), $target);
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
$result = $bot->downloadFile( 'https://httpstat.us/' ); 
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
## App example
See 
* [exampleApp.php](example/exampleApp.php)
* [example03.php](example/example03.php)

## Test running

* install __phpunit, composer, php__ if not installed
* run __composer install__ at package dir
* run __tests/startServer.bat__
* run __tests/css-webserver/startServer.bat__
* run __tests/startTest.bat__

On linux use *.sh files like *.bat files

## Useful links: 
* Cmd windows
	* [WindowsPathEditor](https://rix0rrr.github.io/WindowsPathEditor/)
	* [conemu](https://conemu.github.io/)
* PHP in CMD
	* [windows.php.net](https://windows.php.net/)
	* [xampp](https://www.apachefriends.org/ru/index.html)
	* [phpunit 5](https://phpunit.de/getting-started/phpunit-5.html)
	* [phpunit in bat](https://stackoverflow.com/questions/24861233/phpunit-setup-in-batch-file)
	* [composer in bat](http://leedavis81.github.io/global-installation-of-composer-on-windows/)
* Git client
	* [git](https://gitforwindows.org/)
	* [smartgit](https://www.syntevo.com/smartgit/)

## More features
For more features:
* Read source of [HttpClient](src/HttpClient.php) class
* Read [CURL](https://www.php.net/curl) documentation
* Read source code and examples
* Practice with `Parser` in real code