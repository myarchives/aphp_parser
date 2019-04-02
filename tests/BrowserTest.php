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
		$this->browser = new Browser( $this->userAgent, __DIR__ . '/webserver' );
		@unlink( $this->browser->getTempFileName() );
	}
	
	protected function tearDown() {
		$this->browser->client->close();
		@unlink( $this->browser->getTempFileName() );
	}
	
	// tests
	
	public function test_navigate() {
		$this->browser->navigate('http://localhost:8008/200.php');
		
		$this->assertTrue( $this->browser->isNavigateSucceed() );
		$this->assertContains( $this->userAgent, $this->browser->getData() );
	}

	public function test_navigateError() {
		$this->browser->navigate('http://localhost:8008/404.php');
		
		$this->assertTrue( $this->browser->isNavigateSucceed() == false );
	}

	public function test_downloadFile() {
		$this->browser->downloadFile('http://localhost:8008/png-image.png');

		$this->assertTrue( $this->browser->isDownloadSucceed() );

		$this->assertFileEquals( $this->browser->getTempFileName(), __DIR__ . '/webserver/png-image.png');
	}

	public function test_downloadFileError() {
		$this->browser->downloadFile('http://localhost:8010/png-image.png');

		$this->assertTrue( $this->browser->isDownloadSucceed() == false );
	}

	public function test_downloadImage() {
		$this->browser->downloadImage('http://localhost:8008/png-image.png');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( '.png', $this->browser->getImageFileExt() );

		$this->browser->downloadImage('http://localhost:8008/jpg-image.jpg');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( '.jpg', $this->browser->getImageFileExt() );

		$this->browser->downloadImage('http://localhost:8008/gif-image.gif');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( '.gif', $this->browser->getImageFileExt() );

		$this->browser->downloadImage('http://localhost:8008/svg-image.svg');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( '.svg', $this->browser->getImageFileExt() );
	}

	public function test_navigate2() {
		$this->browser->navigate('http://localhost:8008/200.php');
		
		$this->assertTrue( $this->browser->isNavigateSucceed() );
		$this->assertContains( $this->userAgent, $this->browser->getData() );

		$this->browser->downloadImage('http://localhost:8008/png-image.png');
		$this->assertTrue( $this->browser->isDownloadSucceed() );
		$this->assertEquals( '.png', $this->browser->getImageFileExt() );

		$this->browser->navigate('http://localhost:8008/200.php');
		
		$this->assertTrue( $this->browser->isNavigateSucceed() );
		$this->assertContains( $this->userAgent, $this->browser->getData() );
	}
}