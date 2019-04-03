<?php

namespace aphp\Parser;

abstract class BotH {
	public $retryCount = 5;
	public $sleepTimeout = 3;
	public $browsers = []; // [ Browser ]

	public $tempDir;
	public $prefix = '';

	public $currentBrowser; // Browser

	abstract public function add_proxy_http($proxy, $userAgent);
	abstract public function add_proxy_socks4($proxy, $userAgent);

	abstract public function navigate ( $url );
	abstract public function downloadImage( $url );
	abstract public function downloadFile( $url );

	abstract public function runProxyTest( $url ); // example https://www.uptimeinspector.com/test-server-connection.html
}

// ------------------------
// Bot
// ------------------------

class Bot extends BotH {
	use \Psr\Log\LoggerAwareTrait; // trait

	// PROTECTED

	protected function addBrowser($userAgent) {
		$browser = new Browser($userAgent, $this->tempDir, $this->prefix . count($this->browsers) . '_');
		if ($this->logger) 
			$browser->setLogger($this->logger);
		$this->browsers[] = $browser;
		$this->currentBrowser = $this->browsers[0];
		return $browser;
	}

	protected function loggerInfo($text) {
		if ($this->logger) {
			$this->logger->info($text);
		}
	}

	// Override
	public function setLogger(\Psr\Log\LoggerInterface $logger) {
		$this->logger = $logger;
	}

	// PUBLIC

	public function __construct( $tempDir, $prefix = 'browser' ) {
		$this->tempDir = $tempDir;
		$this->prefix  = $prefix;
	}

	public function add_proxy_http($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		$this->loggerInfo("add_proxy_http $proxy");
		$browser->client->set_proxy_http($proxy);
	}

	public function add_proxy_socks4($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		$this->loggerInfo("add_proxy_socks4 $proxy");
		$browser->client->set_proxy_socks4($proxy);
	}

	public function navigate ( $url ) {
		return $this->runTask('navigate', $url);
	}

	public function downloadImage ( $url ) {
		return $this->runTask('downloadImage', $url);
	}

	public function downloadFile ( $url ) {
		return $this->runTask('downloadFile', $url);
	}

	public function getBrowser() {
		return $this->currentBrowser->browser;
	} 

	public function runProxyTest( $url ) {
		$browserList = [];
		foreach ($this->browsers as $browser) {
			if ($browser->navigate( $url )) {
				$this->loggerInfo("proxyTest OK : {$browser->prefix}");
				$browserList[] = $browser;
			} else {
				$this->loggerInfo("proxyTest F : {$browser->prefix}");
			}
		}
		$this->browsers = $browserList;
	}

	// TASK

	protected function nextProxy() {
		$i = array_search($this->currentBrowser, $this->browsers);
		$i++;
		if ($i>=count($this->browsers)) {
			$i = 0;
		}
		$this->currentBrowser = $this->browsers[$i];
		$this->loggerInfo("nextBrowser : {$this->currentBrowser->prefix}");
	}

	protected function runTask($task, $url) {
		if (count($this->browsers) == 0) {
			throw new NoProxy_Exception();
		}
		$this->loggerInfo("START $task : $url");
		$retryCount = $this->retryCount;
		while ($retryCount > 0) {
			$result = $this->currentBrowser->{$task}($url);
			if ($result) {
				if ($this->sleepTimeout > 0) {
					sleep($this->sleepTimeout);
				}
				$this->loggerInfo("FINISH $task : $url");
				return true;
			}
			$retryCount--;
			$this->nextProxy();
			if ($this->sleepTimeout > 0) {
				sleep($this->sleepTimeout);
			}
		}
		$this->loggerInfo("FAIL $task : $url");
		return false;
	}
}