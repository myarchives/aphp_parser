<?php

/*
https://phpunit.de/getting-started/phpunit-5.html

PHP 5.6

All tests
phpunit --bootstrap tests/bootstrap_autoload.php tests --debug

One file
phpunit --bootstrap tests/bootstrap_autoload.php tests/%file%Test.php --debug

One test
phpunit --bootstrap tests/bootstrap_autoload.php --filter %method% tests/%file%Test.php --debug
*/

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/mock/MockClient.php';
require __DIR__ . '/mock/MockApp.php';

class Base_TestCase extends PHPUnit_Framework_TestCase {

}


