# Parser

![PHP Support](https://img.shields.io/badge/php-5.6-brightgreen.svg)
![PHP Support](https://img.shields.io/badge/php-7-brightgreen.svg)
![License](https://img.shields.io/badge/license-MIT-green.svg)
![Travis](https://api.travis-ci.org/GonistLelatel/aphp_parser.svg?branch=master)

## Introduction

`Parser` is a curl browser with automatic proxy reconnect logic.

* See [php.net/curl](https://www.php.net/curl) for more information about the libcurl extension for PHP.
* See [aphp/http-client](https://github.com/GonistLelatel/aphp_httpclient)

## Installation

### Version 2
Recomended

`composer require aphp/parser ~2.0.0`

### Version 1
Vesion 1 is not flexible and used only for non-auth parsing. Used http-client ~1.0.0 .<br>
https://github.com/GonistLelatel/aphp_parser/tree/1.0.9

`composer require aphp/parser ~1.0.0`

## Usage

Usage for parser is not simple, because its needed the application with container architecture to resolve constructor dependencies.

### App example

* See [ExampleApp.php](example2/app/ExampleApp.php)
* See [phpbbauth.php](example2/phpbbauth.php)

### Simple example

```php
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
```

## Features

* Proxy reconnect logic.
* Auth logic.
* Usage examples.

## Syntax

BotSettings is using for store setting.

```php
class BotSettings
{
    public $retryCount = 10;
    public $retryPerProxy = 3;
    public $sleepTimeout = 0.5;
    public $newIPCookies = true;
    public $newUserAgent = true;
    public $currentProxyIndex = 0;

    public $proxyList = [];
    public $userAgentList = [];
    public $browserConfig = null; // BrowserConfig
    public $authLogic = null; // AuthLogic
}
```
### AuthLogic
AuthLogic is using for custom auth logic.<br>
AuthLogic use the `Closure` architecture, not inheritance.<br>
All properties are public and can be re-set.
```php
class AuthLogic
{
    public $_isAuth = false;
    public $_bot = null; // Bot

    public function __construct()
    {
        $this->isAuth = function() {
            return $this->_isAuth;
        };
        $this->doAuth = function() {
            // logic
            $this->_isAuth = true;
            return true;
        };
        // Events
        $this->resetCookiesEvent = function() {
            $this->_isAuth = false;
        };
        $this->responseEvent = function() {
            $code = $this->_bot->client->get_http_response_code();
            if ($code >= 401 && $code < 500 && $code != 429) {
                sleep(1);
                return true;
            }
            return false;
        };
        $this->failEvent = function() {
            // logic
            return false;
        };
    }
}
```
See example of authlogic usage:
* [phpbbauth.php](example2/phpbbauth.php)

### Bot

Bot is instance of `Browser` with `runTask` functions and logic.

```php
class Bot extends Browser
{
	public $botSettings = null; // BotSettings
	public $tasks = [];

	// ---------

	public function __construct(BotSettings $botSettings);
	public function nextConfig();

	// --------

	public function runTaskNoChangeProxy(/*Closure*/ $task, $type = 'task-noChangeProxy', $name = '');
	public function runTaskAuth(/*Closure*/ $task, $type = 'task-a', $name = '');
	public function runTask(/*Closure*/ $task, $type = 'task', $name = '');

	public function lastTask();
}
```

Example tasks:

```php
// try count = 3 (default), proxy not changed
$r = $bot->runTaskNoChangeProxy(function() use($bot){
    return $bot->navigate('https://httpstat.us');
});
// try count = setting->retryPerProxy * setting->retryCount = 3*10 = 30
// proxy is changed on fail
$r = $bot->runTask(function() use($bot){
    return $bot->navigate('https://httpstat.us');
});
// try count = setting->retryPerProxy * setting->retryCount = 3*10 = 30
// proxy is changed on fail,
// if authLogic->isAuth() == false, then authLogic->doAuth()
$r = $bot->runTaskAuth(function() use($bot){
    return $bot->navigate('https://httpstat.us');
});
```
See the browser documentation: [aphp/http-client](https://github.com/GonistLelatel/aphp_httpclient)

## Test running

* install __phpunit, composer, php__ if not installed
* __composer install__ at package dir
* __composer run-script test__

<details><summary><b>&#x1F535; Useful links</b></summary>
<p>

* Composer package generator
	* [projectGen2](https://github.com/GonistLelatel/projectGen2)
* Cmd windows
	* [WindowsPathEditor](https://rix0rrr.github.io/WindowsPathEditor/)
	* [conemu](https://conemu.github.io/)
* PHP downloads
	* [windows.php.net](https://windows.php.net/)
	* [xampp](https://www.apachefriends.org/ru/index.html)
	* [openserver](https://open-server.soft112.com/)
* PHP installations
	* [install-php-on-windows](https://www.utilizewindows.com/install-php-on-windows/)
	* [phpunit 5](https://phpunit.de/getting-started/phpunit-5.html)
	* [phpunit in bat](https://stackoverflow.com/questions/24861233/phpunit-setup-in-batch-file)
	* [composer in bat](http://leedavis81.github.io/global-installation-of-composer-on-windows/)
* Git client
	* [git](https://gitforwindows.org/)
	* [smartgit](https://www.syntevo.com/smartgit/)

</p>
</details>

## More features
For more features:
* Read [CURL](https://www.php.net/curl) documentation
* Read source code and examples
* Practice with `Parser` in real code