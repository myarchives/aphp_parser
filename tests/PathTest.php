<?php 

use aphp\Parser\Path;

class PathTest extends Base_TestCase {
	
	// STATIC
	public static function setUpBeforeClass() {
		
	}

	public static function tearDownAfterClass() {
		
	}

	// Override
	
	protected function setUp() {

	}
	
	protected function tearDown() {

	}

	// Test

	public function test_url() {
		// http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/style.css
		$path = new Path('http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/');

		$this->assertEquals($path->url, 'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/');
		$this->assertEquals($path->absPath(), 'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/');
		$this->assertEquals($path->domainPath(), 'http://d3g0gp89917ko0.cloudfront.net/');

		$path2 = new Path('https://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/file.css');

		$this->assertEquals($path2->url, 'https://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/file.css');
		$this->assertEquals($path2->absPath(), 'https://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/');
		$this->assertEquals($path2->domainPath(), 'https://d3g0gp89917ko0.cloudfront.net/');
	}

	/**
	  * @expectedException     aphp\Parser\Path_Exception
	*/
	public function test_url_f() {
		$path = new Path('d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/');
		$this->assertTrue(false);
	}

	
	public function test_relativeToAbsolute() {
		$path = new Path('http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/style.css');

		$this->assertEquals(
			$path->relativeToAbsolute('image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('hTTP://domain.net'),
			'hTTP://domain.net'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('./image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('../image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('../../image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('../../../image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('../path/../image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('/image.png'),
			'http://d3g0gp89917ko0.cloudfront.net/image.png'
		);
		$this->assertEquals(
			$path->relativeToAbsolute('/home/'),
			'http://d3g0gp89917ko0.cloudfront.net/home/'
		);
	}

}

