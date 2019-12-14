<?php
namespace aphp\Parser;
//use aphp\HttpClient\BrowserConfig;

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

	public function __construct()
	{
		$this->userAgentList = $this->defaultUserAgentList();
		$this->authLogic = new AuthLogic;
	}

	public function configForProxy($proxy)
	{
		$config = clone $this->browserConfig;
		$config->_proxy = $proxy;
		$index = array_keys($this->proxyList, $proxy)[0];
		if ($this->newIPCookies) {
			$config->browserName = 'bot' . $index;
		}
		if ($this->newUserAgent) {
			$config->userAgent = $this->userAgentList[ $index % count($this->userAgentList) ];
		}
		return $config;
	}
	public function currentProxy()
	{
		return $this->proxyList[ $this->currentProxyIndex ];
	}
	public function nextProxy()
	{
		$this->currentProxyIndex = ($this->currentProxyIndex + 1) % count($this->proxyList);
		return $this->currentProxy();
	}

	// protected

	protected function defaultUserAgentList()
	{
		$txt = file_get_contents( __DIR__ . '/../textFiles/useragents.txt' );
		$txt = explode("\n", $txt);
		$txt = array_map('trim', $txt);
		return $txt;
	}
}