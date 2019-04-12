<?php
// www/routing.php
//
// https://silex.symfony.com/doc/2.0/web_servers.html#php-5-4
//

$filename = __DIR__ . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
} else {
	include __DIR__ . '/index.php';
}