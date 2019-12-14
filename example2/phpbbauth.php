<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/app/ExampleApp.php';

use aphp\DOM\Dom;

$app       = ExampleApp::getInstance();
$baseURL   = 'https://www.phpbb.com/community';
$bot       = $app->bot;
$settings  = $bot->botSettings;
$authLogic = $bot->botSettings->authLogic;

// url

function __url($url) {
	global $baseURL;
	$url = ltrim($url, '.');
	$url = str_replace('&amp;', '&', $url);
	return $baseURL . $url;
}

// login password

$login    = @$app->argv->namedParams['login'];
$password = @$app->argv->namedParams['password'];

if (!is_string($login) || !is_string($password)) {
	echo 'please enter login and password' . PHP_EOL;
	echo 'syntax: # phpbbauth.php --login %login% --password %password%' . PHP_EOL;
	exit;
}

// ---

$authLogic->doAuth = function() use($bot, $authLogic, $login, $password, $baseURL)
{
	// set default cookies
	$r = $bot->runTask(function() use($bot) {
		return $bot->navigate( __url('/') );
	});
	if (!$r) return false;

	// parse login url
	$dom = new Dom;
	$dom->loadStr( $bot->getData() );
	$a = $dom->find('li.rightside a[href*=mode=login]'); // first link
	if (!$a) return false;

	$login_href = $a->getAttribute('href');

	// navigate login page
	$r = $bot->runTaskNoChangeProxy(function() use($bot, $login_href) {
		return $bot->navigate( __url($login_href) );
	});
	if (!$r) return false;

	// parse login post data
	$postData = [];

	$dom->loadStr( $bot->getData() );
	$input = $dom->findNodes('input[type=hidden]');
	foreach ($input as $ii) {
		$postData[ $ii->getAttribute('name') ] = $ii->getAttribute('value');
	}
	$postData['username'] = $login;
	$postData['password'] = $password;

	// <input type="submit" name="login" tabindex="6" value="Login" class="button1">
	$postData['login'] = 'Login';

	print_r($postData);

	// login request
	$r = $bot->runTaskNoChangeProxy(function() use($bot, $postData) {
		return $bot->navigate( __url('/ucp.php?mode=login'), $postData);
	});
	if (!$r) return false;

	// check login
	if (strpos($bot->getData(), 'Private messages ') === false) return false;

	// success
	$authLogic->_isAuth = true;
	return true;
};

// --

$r = $bot->runTaskAuth(function() use($bot){
	return $bot->navigate( __url('/') );
});

if (!$r) {
	echo 'failed';
} else {
	file_put_contents(__DIR__ . '/phpbbforum_indexpage.html', $bot->getData() );
	echo 'page saved to phpbbforum_indexpage.html';
}


