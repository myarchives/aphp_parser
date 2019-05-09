<?php
namespace aphp\Parser;
use aphp\Foundation\SystemService;
use aphp\Files\FilePath;
use aphp\Files\File;

abstract class BotH {
	public $browsers = []; // [ Browser ]
 
	public $currentSettings; // BotSettings
	public $settingsDefault; // BotSettings
	public $settingsCSS; // BotSettings

	public $tempDir;
	public $prefix = '';

	public $currentBrowser; // Browser

	abstract public function setConfig(Config $config);

	abstract public function add_proxy_http($proxy, $userAgent);
	abstract public function add_proxy_socks4($proxy, $userAgent);
	abstract public function addBrowser($userAgent);

	abstract public function navigate ( $url );
	abstract public function downloadFile( $url );
	abstract public function downloadCSSResources( $url, $resourceDir, $root = true, $inputParser = null, $res = false );

	abstract public function nextProxy();

	abstract public function runProxyTest( $url );
}

// ------------------------
// Bot
// ------------------------

class Bot extends BotH {
	use \Psr\Log\LoggerAwareTrait; // trait

	// PROTECTED

	public function addBrowser($userAgent) {
		if (count($this->browsers) >= $this->currentSettings->maxProxyCount) {
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
		// settings
		$this->currentSettings = new BotSettings();
		$this->settingsDefault = $this->currentSettings;
		$this->settingsCSS = new BotSettings();
	}

	public function setConfig(Config $config) {
		$proxyList = explode("\n", $config->proxyURLs_text);
		$userAgentList = new UserAgentList($config->userAgentList);
		foreach ($proxyList as $proxy) {
			$browser = $this->add_proxy_http(trim($proxy), $userAgentList->getAgent());
			if ($browser) {
				$browser->client->urlTimeout  = $config->urlTimeout;
				$browser->client->fileTimeout = $config->fileTimeout;
			}
		}
		$this->settingsDefault = $config->botSettings_default();
		$this->settingsCSS = $config->botSettings_css();
		$this->currentSettings = $this->settingsDefault;
	}

	public function add_proxy_http($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		if (!$browser) return null;
		$this->loggerInfo("add_proxy_http $proxy");
		$browser->client->set_proxy_http($proxy);
		$browser->proxyName = $proxy;
		return $browser;
	}

	public function add_proxy_socks4($proxy, $userAgent) {
		$browser = $this->addBrowser($userAgent);
		if (!$browser) return null;
		$this->loggerInfo("add_proxy_socks4 $proxy");
		$browser->client->set_proxy_socks4($proxy);
		$browser->proxyName = $proxy;
		return $browser;
	}

	public function navigate ( $url ) {
		return $this->runTask('navigate', $url);
	}

	public function downloadFile ( $url ) {
		return $this->runTask('downloadFile', $url);
	}

	public function runProxyTest( $url, $limit = 30 ) {
		$browserList = [];
		$sleepTimeout = $this->currentSettings->sleepTimeout;
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
					SystemService::sleep($sleepTimeout);
				}
			} else {
				$this->loggerInfo("proxyTest F : {$browser->proxyName}");
			}
		}
		$this->browsers = $browserList;
	}

	public function downloadCSSResources( $url, $resourceDir, $root = true, $inputParser = null, $res = false ) {
		if (!$root) {
			// set css settings
			$this->currentSettings = $this->settingsCSS;
		}
		if ($this->downloadFile($url)) {
			// file
			$filePath = new FilePath( Path::filteredUrl($url) );
			if (!$filePath->extension()) {
				$filePath->replaceExtension('html');
			}
			if ($root) {
				$filePath->replacePath($resourceDir);
			} else {
				$filePath->replacePath($resourceDir . '/res');
				$filePath->replaceFileName( md5_file($this->currentBrowser->getTempFileName()) , false);
			}
			$this->currentBrowser->getTempFilePath()->copy($filePath);
			// inputParser
			if ($inputParser) {
				$inputParser->mapFileToLink($url, ($res ? 'res/' : '') . $filePath->fileName() );
			}
			// resources
			if ($root || $this->currentBrowser->getTempFile()->isText() ) {
				$styleParser = new StyleParser();
				$text = file_get_contents( $filePath->getPath() );
				if ($root) {
					$links = $styleParser->parseHTMLLinks($url, $text);
				} else {
					$links = $styleParser->parseCSSLinks($url, $text);
				}
				foreach ($links as $link) {
					$this->downloadCSSResources($link, $resourceDir, false, $styleParser, $root);
				}
				$text = $styleParser->replaceLinksInText($text);
				file_put_contents($filePath->getPath(), $text);
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

	protected function runTask($task, $url) {
		if (count($this->browsers) == 0) {
			throw new NoProxy_Exception();
		}
		$this->loggerInfo("START $task : $url");
		$retryCount = $this->currentSettings->retryCount;
		$sleepTimeout = $this->currentSettings->sleepTimeout;
		while ($retryCount > 0) {
			$result = $this->currentBrowser->{$task}($url);
			if ($result) {
				if ($sleepTimeout > 0) {
					SystemService::sleep($sleepTimeout);
				}
				$this->loggerInfo("FINISH $task : $url");
				// settings reset
				if ($this->currentSettings != $this->settingsDefault) {
					$this->currentSettings = $this->settingsDefault;
				}
				return true;
			}
			$code = $this->currentBrowser->client->get_http_response_code();
			if ($code >= 401 && $code < 500 && $code != 429) {
				if ($sleepTimeout > 0) {
					SystemService::sleep($sleepTimeout);
				}
				return false;
			}
			$retryCount--;
			$this->nextProxy();
			if ($sleepTimeout > 0) {
				SystemService::sleep($sleepTimeout);
			}
		}
		$this->loggerInfo("FAIL $task : $url");
		// settings reset
		if ($this->currentSettings != $this->settingsDefault) {
			$this->currentSettings = $this->settingsDefault;
		}
		return false;
	}
}