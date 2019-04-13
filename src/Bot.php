<?php

namespace aphp\Parser;

abstract class BotH {
	protected $_resetConfigAfterTask = false;

	public $retryCount = [ 10 ];
	public $sleepTimeout = [ 3 ];
	public $maxProxyCount = 50;

	public $retryCount_CSSResources = 4;
	public $browsers = []; // [ Browser ]

	public $tempDir;
	public $prefix = '';

	public $currentBrowser; // Browser

	abstract public function setConfig(Config $config);

	abstract public function add_proxy_http($proxy, $userAgent);
	abstract public function add_proxy_socks4($proxy, $userAgent);
	abstract public function addBrowser($userAgent);

	abstract public function navigate ( $url );
	abstract public function downloadImage( $url );
	abstract public function downloadFile( $url );
	abstract public function downloadCSSResources( $url, $resourceDir, $root = true, $inputParser = null, $res = false );

	abstract public function nextProxy();
	abstract public function resetConfig();

	abstract public function runProxyTest( $url );
}

// ------------------------
// Bot
// ------------------------

class Bot extends BotH {
	use \Psr\Log\LoggerAwareTrait; // trait

	// PROTECTED

	public function addBrowser($userAgent) {
		if (count($this->browsers) >= $this->maxProxyCount) {
			return null;
		}
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

	public function setConfig(Config $config) {
		$proxyList = explode("\n", $config->proxyURLs_text);
		$userAgentList = new UserAgentList($config->userAgentList);
		foreach ($proxyList as $proxy) {
			$this->add_proxy_http(trim($proxy), $userAgentList->getAgent());
		}
		$this->retryCount[0]   = $config->retryCount;
		$this->sleepTimeout[0] = $config->sleepTimeout;
		$this->retryCount_CSSResources = $config->retryCount_CSSResources;
	}

	public function add_proxy_http($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		if (!$browser) return;
		$this->loggerInfo("add_proxy_http $proxy");
		$browser->client->set_proxy_http($proxy);
		$browser->proxyName = $proxy;
	}

	public function add_proxy_socks4($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		if (!$browser) return;
		$this->loggerInfo("add_proxy_socks4 $proxy");
		$browser->client->set_proxy_socks4($proxy);
		$browser->proxyName = $proxy;
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

	public function runProxyTest( $url, $limit = 30 ) {
		$browserList = [];
		$sleepTimeout = $this->sleepTimeout[ count($this->sleepTimeout)-1 ];
		foreach ($this->browsers as $browser) {
			if ($browser->navigate( $url )) {
				$this->loggerInfo("proxyTest OK : {$browser->proxyName}");
				$browserList[] = $browser;
				$limit--;
				if ($limit < 0) {
					$this->loggerInfo("proxyTest limit OK : {$browser->proxyName}");
					break;
				}
				if ($sleepTimeout > 0) {
					sleep($sleepTimeout);
				}
			} else {
				$this->loggerInfo("proxyTest F : {$browser->proxyName}");
			}
		}
		$this->browsers = $browserList;
	}

	public function downloadCSSResources( $url, $resourceDir, $root = true, $inputParser = null, $res = false ) {
		// set config
		if (!$root) {
			$this->retryCount[] = $this->retryCount_CSSResources;
			$this->_resetConfigAfterTask = true;
		}
		if ($this->downloadFile($url)) {
			// res dir
			if (!is_dir($resourceDir . '/res')) {
				mkdir($resourceDir . '/res');
			}
			// file
			$ext =  Path::extractExt($url);
			if ($root) {
				$dir = $resourceDir;
				$fileName = Path::filteredUrl($url) . ($ext=='.bin' ? $ext : '.html');
			} else {
				$dir = $resourceDir . '/res';
				$fileName = md5_file($this->currentBrowser->getTempFileName()). $ext;
			}
			$dirFileName = $dir . '/' . $fileName;
			copy($this->currentBrowser->getTempFileName(), $dirFileName);
			// inputParser
			if ($inputParser) {
				$inputParser->mapFileToLink($url, ($res ? 'res/' : '') . $fileName);
			}
			$mime = $this->currentBrowser->getTempFileMime();
			// resources
			if ($root || (strpos($mime, 'text/') !== false)) {
				$styleParser = new StyleParser();
				$text = file_get_contents($dirFileName);
				if ($root) {
					$links = $styleParser->parseHTMLLinks($url, $text);
				} else {
					$links = $styleParser->parseCSSLinks($url, $text);
				}
				foreach ($links as $link) {
					$this->downloadCSSResources($link, $resourceDir, false, $styleParser, $root);
				}
				$text = $styleParser->replaceLinksInText($text);
				file_put_contents($dirFileName, $text);
			}
			return true;
		}
		return false;
	}
	// TASK

	public function nextProxy() {
		$i = array_search($this->currentBrowser, $this->browsers);
		$i++;
		if ($i>=count($this->browsers)) {
			$i = 0;
		}
		$this->currentBrowser = $this->browsers[$i];
		$this->loggerInfo("nextBrowser : {$this->currentBrowser->proxyName}");
	}

	public function resetConfig() {
		$this->_resetConfigAfterTask = false;
		$this->retryCount = [ $this->retryCount[0] ];
		$this->sleepTimeout = [ $this->sleepTimeout[0] ];
	}

	protected function runTask($task, $url) {
		if (count($this->browsers) == 0) {
			throw new NoProxy_Exception();
		}
		$this->loggerInfo("START $task : $url");
		$retryCount = $this->retryCount[ count($this->retryCount)-1 ];
		$sleepTimeout = $this->sleepTimeout[ count($this->sleepTimeout)-1 ];
		while ($retryCount > 0) {
			$result = $this->currentBrowser->{$task}($url);
			if ($result) {
				if ($sleepTimeout > 0) {
					sleep($sleepTimeout);
				}
				$this->loggerInfo("FINISH $task : $url");
				if ($this->_resetConfigAfterTask) {
					$this->resetConfig();
				}
				return true;
			}
			$code = $this->currentBrowser->client->get_http_response_code();
			if ($code == 404) {
				if ($sleepTimeout > 0) {
					sleep($sleepTimeout);
				}
				return false;
			}
			$retryCount--;
			$this->nextProxy();
			if ($sleepTimeout > 0) {
				sleep($sleepTimeout);
			}
		}
		$this->loggerInfo("FAIL $task : $url");
		if ($this->_resetConfigAfterTask) {
			$this->resetConfig();
		}
		return false;
	}
}