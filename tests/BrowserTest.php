<?php 

use aphp\Parser\Browser;

class BrowserTest extends Base_TestCase {
	
	// STATIC
	public static function setUpBeforeClass() {
		
	}

	public static function tearDownAfterClass() {
		
	}
	
	// override
	
	protected $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
	protected $browser;
	
	protected function setUp() {
		$this->browser = new Browser( $this->userAgent, __DIR__ . '/temp' );
		@unlink( $this->browser->getTempFileName() );
	}
	
	protected function tearDown() {
		$this->browser->client->close();
		@unlink( $this->browser->getTempFileName() );
	}
	
	// tests
	public function test_downloadImage() {
		$this->browser->downloadFile('http://localhost:8008/png-image.png');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( 'png', $this->browser->getTempFile()->mimeExtension() );

		$this->browser->downloadFile('http://localhost:8008/jpg-image.jpg');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( 'jpg', $this->browser->getTempFile()->mimeExtension() );
	}
}