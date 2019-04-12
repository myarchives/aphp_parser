<?php 

class BotTest extends Base_TestCase {

	// STATIC
	public static function setUpBeforeClass() {
	
	}

	public static function tearDownAfterClass() {
		
	}
	
	// override
	
	protected $userAgent = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
	protected $bot;
	
	protected function setUp() {
		$this->bot = new mockBot( __DIR__ . '/temp' );
		$this->bot->add_proxy_http('78.40.87.18:801', $this->userAgent);
		$this->bot->add_proxy_http('78.40.87.18:802', $this->userAgent);
		$this->bot->add_proxy_http('78.40.87.18:803', $this->userAgent);
		$this->bot->add_proxy_http('78.40.87.18:804', $this->userAgent);
	}
	
	protected function tearDown() {

	}

	// TEST

	public function test_proxyTest() {
		$this->bot->sleepTimeout[] = 0;
		$this->bot->runProxyTest('https://google.com');
		$count = count($this->bot->browsers);

		$this->assertTrue( $count == 4 );
		$this->bot->browsers[1]->_nextResult = false;

		$this->bot->runProxyTest('https://google.com');
		$count = count($this->bot->browsers);
		$this->assertTrue( $count == 3 );
	}
	
	public function test_navigate1() {
		$this->bot->sleepTimeout[] = 0;
		$this->bot->retryCount[] = 4;

		$this->bot->browsers[0]->_nextResult = false;
		$this->bot->browsers[1]->_nextResult = false;
		$this->bot->browsers[2]->_nextResult = false;
		$this->bot->browsers[3]->_nextResult = true;

		$result = $this->bot->navigate( '' );

		$this->assertTrue( $this->bot->currentBrowser->prefix == 'browser3_' );
		$this->assertTrue( $result );

		$this->bot->browsers[0]->_nextResult = false;
		$this->bot->browsers[1]->_nextResult = true;
		$this->bot->browsers[2]->_nextResult = true;
		$this->bot->browsers[3]->_nextResult = true;
		$this->bot->currentBrowser = $this->bot->browsers[0];

		$result = $this->bot->navigate( '' );

		$this->assertTrue( $this->bot->currentBrowser->prefix == 'browser1_' );
		$this->assertTrue( $result );
	}

	public function test_error() {
		$this->bot->sleepTimeout[] = 0;
		$this->bot->retryCount[] = 4;

		$this->bot->browsers[0]->_nextResult = false;
		$this->bot->browsers[1]->_nextResult = false;
		$this->bot->browsers[2]->_nextResult = false;
		$this->bot->browsers[3]->_nextResult = false;

		$result = $this->bot->navigate( '' );

		$this->assertTrue( $this->bot->currentBrowser->prefix == 'browser0_' );
		$this->assertTrue( $result == false );
	}

	/**
    * @expectedException aphp\Parser\NoProxy_Exception
    */
	public function test_proxyTest_error() {
		$this->bot->sleepTimeout[] = 0;
		$this->bot->browsers[0]->_nextResult = false;
		$this->bot->browsers[1]->_nextResult = false;
		$this->bot->browsers[2]->_nextResult = false;
		$this->bot->browsers[3]->_nextResult = false;

		$this->bot->runProxyTest('https://google.com');

		$result = $this->bot->navigate( '' );
		$this->assertTrue( $result == false );
	}
}