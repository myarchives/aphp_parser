<?php
require_once __DIR__ . '/../vendor/autoload.php';

class exampleApp extends aphp\Foundation\Container 
{
	public function __construct() {
		$this->init();
	}

	public function downloadProxyListIfNeeded() {
		if (!is_file($this->proxyList)) {
			if ($this->browser->navigate('https://www.proxy-list.download/api/v0/get?l=en&t=https')) {
				$html = $this->browser->getData();
				$json = json_decode($html,true);
				$list = $json[0]['LISTA'];
				// set proxy limit as 10
				$list = array_slice($list, 0, 10);
				//
				$proxyList = array_map( function($v){ return $v['IP'] . ':' . $v['PORT']; }, $list);
				file_put_contents($this->proxyList, implode("\n", $proxyList));
			} else {
				throw new Exception('proxyList failed to download');
			}
		}
	}
	
	protected function init() {
		set_time_limit(0);

		$this->dir = __DIR__;
		$this->tempDir = $this->dir . '/temp';
		$this->proxyList = $this->tempDir . '/proxyList.txt';

		$this->logger = function ($c) {
			$logger = new aphp\logger\FileLogger();
			$logger->configure($c->dir . '/logs/log', true, 2048);
			// --
			$logger->filter[] = 'add_proxy_http';
			// --
			$logger->startLog();
			return $logger;
		};

		$this->bot_config = function ($c) {
			$config = new aphp\Parser\Config();
			if (!is_file($this->proxyList)) {
				throw new Exception($c->proxyList . ' is not exist');
			}
			$config->proxyURLs_text = file_get_contents($c->proxyList);
			return $config;
		};

		$this->bot = function ($c) {
			$bot = new aphp\Parser\Bot($c->tempDir);
			$bot->setLogger( $c->logger );
			$bot->setConfig( $c->bot_config );
			return $bot;
		};
		
		$this->browser = function ($c) {
			$browser = new aphp\Parser\Browser(
				'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.67 Safari/537.36',
				$c->tempDir
			);
			$browser->setLogger($c->logger);
			return $browser;
		};
	}
}
