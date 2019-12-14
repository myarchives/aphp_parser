<?php
use aphp\Files\FilePath;
use aphp\Foundation\Container;
use aphp\HttpClient\BrowserConfig;
use aphp\Logger\FileLogger;
use aphp\Parser\AuthLogic;
use aphp\Parser\Bot;
use aphp\Parser\BotSettings;

class MockApp extends Container
{
	use aphp\Foundation\TraitSingleton;

	public function __construct()
	{
		parent::__construct();

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

		$this->proxyList = function() {
			$txt = file_get_contents( __DIR__ . '/mock_proxyList.txt' );
			$txt = explode("\n", $txt);
			$txt = array_map('trim', $txt);
			return $txt;
		};

		$this->settings = function() {
			$settings = new BotSettings();
			$settings->browserConfig = new BrowserConfig( $this->tempDir );
			$settings->proxyList = $this->proxyList;
			return $settings;
		};

		$this->logger = function() {
			$logger = new FileLogger();
			$logger->configure( $this->logDir . '/log');
			$logger->startLog();
			return $logger;
		};

		$this->bot = function() {
			$bot = new Bot( $this->settings );
			$bot->setLogger( $this->logger );
			$bot->client = new MockClient();
			return $bot;
		};
	}

	public function clientSet($ret, $status) {
		$this->bot->client->_get_http_response_code = $status;
		$this->bot->client->_fetch_get = $ret;
		$this->bot->client->_fetch_post = $ret;
	}

	public function resetAuthLogic() {
		$this->bot->botSettings->authLogic = new AuthLogic;
		$this->bot->botSettings->currentProxyIndex = -1;
		$this->bot->nextConfig();
	}
}