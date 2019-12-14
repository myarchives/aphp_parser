<?php

class BotTest extends Base_TestCase
{
	public function testPositiveCase()
	{
		$app = MockApp::getInstance();
		$app->logger->info('-- STARTED testPositiveCase');

		$bot = $app->bot;

		$app->clientSet('ok', 200);

		$this->assertEquals('#1', $bot->config->_proxy );

		$r = $bot->runTask(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();

		$this->assertEquals('ok', $d);

		$bot->nextConfig();
		$this->assertEquals('#2', $bot->config->_proxy );

		$bot->nextConfig();
		$this->assertEquals('#3', $bot->config->_proxy );

		$app->logger->info('-- FINISHED testPositiveCase');
	}

	public function testProxyNextCase()
	{
		$app = MockApp::getInstance();
		$app->logger->info('-- STARTED testProxyNextCase');

		$app->resetAuthLogic();
		$bot = $app->bot;

		$app->clientSet(null, 200);

		$bot->botSettings->authLogic->failEvent = function() use($app) {
			$app->clientSet('ok', 200);
			return false;
		};

		$this->assertEquals('#1', $bot->config->_proxy );

		$r = $bot->runTask(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();

		$this->assertEquals('#2', $bot->config->_proxy );
		$this->assertEquals('ok', $d);

		$app->logger->info('-- FINISHED testProxyNextCase');
	}

	public function testProxyFailCase()
	{
		$app = MockApp::getInstance();
		$app->logger->info('-- STARTED testProxyFailCase');

		$app->resetAuthLogic();
		$bot = $app->bot;

		$app->clientSet(null, 200);

		$countTry = 0;

		$bot->botSettings->authLogic->failEvent = function() use(&$countTry) {
			$countTry++;
			return false;
		};

		$this->assertEquals('#1', $bot->config->_proxy );

		$r = $bot->runTask(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();

		# 11 proxy
		$this->assertEquals('#11', $bot->config->_proxy );
		$this->assertEquals(10, $countTry );
		$this->assertEquals(null, $d);

		$app->logger->info('-- FINISHED testProxyFailCase');
	}

	public function testPositiveAuth()
	{
		$app = MockApp::getInstance();
		$app->logger->info('-- STARTED testPositiveAuth');
		$app->resetAuthLogic();
		$bot = $app->bot;
		$authLogic = $bot->botSettings->authLogic;

		$countCalledAuth = 0;
		$authLogic->doAuth = function() use($app, $bot, $authLogic, &$countCalledAuth) {
			$app->clientSet('auth ok', 200);
			$r = $bot->runTask(function() use($bot) {
				return $bot->navigate('http://url');
			}, 'doAuth');
			$d = $bot->getData();
			$authLogic->_isAuth = ($d == 'auth ok');
			$countCalledAuth++;
			return $authLogic->_isAuth;
		};

		// ---

		$app->clientSet('no auth', 200);

		$r = $bot->runTask(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();
		$this->assertEquals('no auth', $d);

		// --
		$r = $bot->runTaskAuth(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();
		$this->assertEquals('auth ok', $d);
		$this->assertEquals('1', $countCalledAuth);

		$app->logger->info('-- FINISHED testPositiveAuth');
	}

	public function testPositiveAuthReconnect()
	{
		$app = MockApp::getInstance();
		$app->logger->info('-- STARTED testPositiveAuthReconnect');
		$app->resetAuthLogic();
		$bot = $app->bot;
		$authLogic = $bot->botSettings->authLogic;

		// --
		$app->clientSet(null, 500);

		$authLogic->failEvent = function() use($bot, $app) {
			$lastTask = $bot->lastTask();
			if ($lastTask[1] == 'authLogic->doAuth') {
				$app->clientSet('auth ok', 200);
			}
			return false;
		};

		$authLogic->doAuth = function() use($app, $bot, $authLogic) {
			$r = $bot->runTask(function() use($bot) {
				return $bot->navigate('http://url');
			}, '', 'authLogic->doAuth');
			$d = $bot->getData();
			$authLogic->_isAuth = ($d == 'auth ok');
			$app->clientSet('OKKKK', 200);
			return $authLogic->_isAuth;
		};

		// --

		$r = $bot->runTaskAuth(function() use($bot) {
			return $bot->navigate('http://url');
		});
		$d = $bot->getData();

		$this->assertEquals('OKKKK', $d);
		$app->logger->info('-- FINISHED testPositiveAuthReconnect');
	}
}