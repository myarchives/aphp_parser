<?php 

use aphp\Parser\HttpClient;

class HttpClientTest extends Base_TestCase {
	
	// STATIC
	public static function setUpBeforeClass() {
		
	}

	public static function tearDownAfterClass() {
		
	}
	
	// override
	
	protected function setUp() {
		@unlink(__DIR__ . '/temp/cookie.txt');
	}
	
	protected function tearDown() {
		@unlink(__DIR__ . '/temp/cookie.txt');
	}
	
	// tests
	
	public function test_status200() 
	{
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');

		$data = $client->fetch_get('http://localhost:8008/200.php');
		
		$this->assertContains('sample user agent', $data);
		$client->close();
	}

	public function test_status301() 
	{
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');

		$data = $client->fetch_get('http://localhost:8008/301.php');
		
		$this->assertContains('sample user agent', $data);
		$client->close();
	}

	public function test_status404() {
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');
		$data = $client->fetch_get('http://localhost:8008/404.php');

		$this->assertEquals(null, $data);
		$client->close();
	}

	public function test_statusError() {
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');
		$data = $client->fetch_get('http://localhost:8010/404.php');

		$this->assertEquals(null, $data);
		$client->close();
	}

	public function test_POST() {
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');

		$data = $client->fetch_post('http://localhost:8008/post.php', ['hello' => 'datapost', 'hello2' => 'data2']);

		$this->assertContains('datapost', $data);
		$this->assertContains('data2', $data);
		$client->close();
	}

	public function test_cookie() {
		@unlink(__DIR__ . '/temp/cookie.txt');
		$client = new HttpClient();
		$client->set_user_agent('sample user agent');
		$client->store_cookies( __DIR__ . '/temp/cookie.txt');

		$data = $client->fetch_get('http://localhost:8008/setcookie.php');
		$this->assertContains('cookie1 set', $data);

		$data = $client->fetch_get('http://localhost:8008/setcookie.php');
		$this->assertContains('cookie1 = value', $data);
		$client->close();
		
		$client2 = new HttpClient();
		$client2->set_user_agent('sample user agent');
		$client2->store_cookies( __DIR__ . '/temp/cookie.txt');

		$data = $client2->fetch_get('http://localhost:8008/setcookie.php');
		$this->assertContains('cookie1 = value', $data);
		$client2->close();
	}
}
	
	