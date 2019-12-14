<?php
namespace aphp\Parser;
use aphp\Foundation\ANullObject;

class Bot extends Browser
{
	public $botSettings = null; // BotSettings
	public $tasks = [];

	// ---------

	public function __construct(BotSettings $botSettings)
	{
		$this->logger = new ANullObject();
		$this->botSettings = $botSettings;
		parent::__construct( $this->botSettings->configForProxy( $this->botSettings->currentProxy() ));
		$this->botSettings->browserConfig = $this->config;
		$this->logger->info("Config proxy: " . $this->config->_proxy);
	}

	public function nextConfig()
	{
		$this->botSettings->nextProxy();
		$this->setConfig( $this->botSettings->configForProxy( $this->botSettings->currentProxy() ));
		$this->logger->info("Config proxy: " . $this->config->_proxy);
	}

	// --------

	public function runTaskNoChangeProxy(/*Closure*/ $task, $type = 'task-noChangeProxy', $name = '')
	{
		return $this->runTask($task, $type, $name);
	}

	public function runTaskAuth(/*Closure*/ $task, $type = 'task-a', $name = '')
	{
		return $this->runTask($task, $type, $name);
	}

	public function runTask(/*Closure*/ $task, $type = 'task', $name = '')
	{
		$retryCount = $this->botSettings->retryCount;
		$retryPerProxy = $this->botSettings->retryPerProxy;
		$sleepTimeout = $this->botSettings->sleepTimeout;
		$authLogic = $this->botSettings->authLogic;
		$authLogic->_bot = $this;
		// --
		$this->taskAdd($type, $name);
		// --
		if ($type == 'task-a' && (\call_user_func($authLogic->isAuth) == false)) {
			if (\call_user_func($authLogic->doAuth) == false) {
				$this->taskFinish(false, 'isAuth = false, doAuth');
				return false;
			}
		}
		// --
		while ($retryCount > 0) {
			$result = \call_user_func($task);
			if ($result) {
				$this->taskFinish(true);
				return $result;
			}
			// --
			if ( \call_user_func( $authLogic->responseEvent) ) {
				$this->taskFinish(false, 'responseEvent');
				return false;
			}
			// --
			$retryPerProxy--;
			if ($retryPerProxy == 0) {
				// --
				if ( \call_user_func( $authLogic->failEvent) ) {
					$this->taskFinish(false, 'failEvent');
					return false;
				}
				// --
				if ($type == 'task-noChangeProxy') {
					$this->taskFinish(false, 'cant try another proxy');
					return false;
				}
				// --
				$retryPerProxy = $this->botSettings->retryPerProxy;
				$this->nextConfig();
				$continue = true;
				// auth is broken
				if (($type == 'task-a') && $this->botSettings->newIPCookies) {
					\call_user_func($authLogic->resetCookiesEvent);
					$continue = \call_user_func($authLogic->doAuth);
				}
				if ($continue) {
					$retryCount--;
					if ($sleepTimeout > 0) $this->sleep($sleepTimeout);
				} else {
					$this->taskFinish(false, 'resetCookiesEvent doAuth');
					return false;
				}
			}
		}
		$this->taskFinish(false, 'retryLimit');
		return false;
	}

	public function lastTask() {
		return @$this->tasks[ count($this->tasks) - 1 ];
	}

	protected function sleep($time)
	{
		\usleep($time * 1000000);
	}

	protected function taskAdd($type, $name)
	{
		$this->tasks[] = [$type, $name];
		$this->logger->info("START $type $name");
	}

	protected function taskFinish($result = false, $method = '')
	{
		$info = array_pop($this->tasks);
		if ($result) {
			$this->logger->info("FINISH ". $info[0] . ' ' . $info[1] . ' ' . $method);
		} else {
			$this->logger->info("FAIL ". $info[0] . ' ' . $info[1] . ' ' . $method);
		}
		$sleepTimeout = $this->botSettings->sleepTimeout;
		if ($sleepTimeout > 0) $this->sleep($sleepTimeout);
	}
}



