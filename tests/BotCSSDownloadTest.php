<?php 

use aphp\Parser\Bot;

class BotCSSDownloadTest extends Base_TestCase {
	// STATIC
	public static function setUpBeforeClass() {
	
	}

	public static function tearDownAfterClass() {
		
	}
	
	// override
	
	protected $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
	protected $bot;
	
	protected function setUp() {
		$this->bot = new Bot( __DIR__ . '/temp' );
		$this->bot->addBrowser( $this->userAgent );
		$this->bot->settingsCSS->sleepTimeout = 0;
	}
	
	protected function tearDown() {

	}

	// tests

	public function test_downloadCSS() {
		$this->bot->downloadCSSResources('http://localhost:8009/', __DIR__ . '/dw');

		$files = scandir(__DIR__ . '/dw');
		$files_res = scandir(__DIR__ . '/dw/res');

		$this->assertEquals($files, [
			'.',
			'..',
			'.gitkeep',
			'httplocalhost8009.bin',
			'res'
		]);
		$this->assertEquals($files_res, [
			'.',
			'..',
			'02af5890eb103790233b125561fc5eeb.css',
			'09f9f7553b03ed5f4df63150a4e3e3c7.png',
			'282d22f146547793edf77b4434b54279.gif',
			'3f47217220b623629561efa0e7c1c120.png',
			'4acae012020b710e90e4c5013016261c.png',
			'5993e47baee60b85cb3bffe11ab7e263.css',
			'624942a1774a21a3174579155445c076.png',
			'67a96aed20bcc6a37c6b5d92105ac4e9.png',
			'778708e5939e40059175ccf9913eed73.gif',
			'c530d50f5ecee00d81a548fa59c246c1.css',
			'f4af8db80f4d12f9e7a3301bfdde447f.png',
			'fdc5400b33cadcea51d711eb81d10d4c.png'
		]);
	}
}