<?php
use aphp\Files\FilePath;
use aphp\HttpClient\BrowserConfig;
use aphp\Logger\FileLogger;
use aphp\Parser\Bot;
use aphp\Parser\BotSettings;
use aphp\Parser\Browser;
use aphp\cli\App;
use function aphp\Foundation\validate;

class ExampleApp extends App
{
	protected function init()
	{
		parent::init();

		$this->tempDir = function() {
			$dir = new FilePath( __DIR__ . '/../temp' );
			$dir->mkdir();
			$dir->delete();
			return $dir->getPath(0);
		};

		$this->logDir = function() {
			$dir = new FilePath( __DIR__ . '/../logs' );
			$dir->mkdir();
			$dir->delete();
			return $dir->getPath(0);
		};

		$this->logger = function() {
			$logger = new FileLogger();
			$logger->configure( $this->logDir . '/log');
			$logger->startLog();
			return $logger;
		};

		$this->proxyList = function() {
			if (!file_exists(__DIR__ . '/proxyList.txt')) {
				$b = new Browser( new BrowserConfig( $this->tempDir ) );
				if ($b->downloadFile('https://www.proxy-list.download/api/v1/get?type=https')) {
					copy( $b->getTempFileName(), __DIR__ . '/proxyList.txt' );
				}
			}
			$proxyList = validate('file_exists', __DIR__ . '/proxyList.txt');
			$proxyList = file_get_contents(__DIR__ . '/proxyList.txt');
			$proxyList = explode("\n", trim($proxyList));
			$proxyList = array_map('trim', $proxyList);
			return $proxyList;
		};

		// --

		$this->settings = function() {
			$settings = new BotSettings();
			$settings->browserConfig = new BrowserConfig( $this->tempDir );
			$settings->proxyList = $this->proxyList;
			return $settings;
		};

		$this->bot = function() {
			$bot = new Bot( $this->settings );
			$bot->setLogger( $this->logger );
			return $bot;
		};
	}
}