<?php 

use aphp\Parser\StyleParser;

class StyleParserTest extends Base_TestCase {
	
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

	// tests

	public function test_parseHTMLLinks() {
		$text = file_get_contents( __DIR__ . '/files/001.html');
		$styleParser = new StyleParser();

		$links = $styleParser->parseHTMLLinks('http://domain.com', $text);

		$this->assertEquals($links,[
			'http://domain.com/component:theme/2',
			'http://domain.com/component:theme/3/',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--modules/css/wiki/pagestagcloud/PagesTagCloudModule.css',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/style.css',
			'http://domain.wdfiles.com/local--code/component:theme/1'
		]);
	}

	public function test_parseCSSLinks() {
		$text = file_get_contents( __DIR__ . '/files/002.css');
		$styleParser = new StyleParser();
		$links = $styleParser->parseCSSLinks('http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/css/style.css', $text);
		$this->assertEquals($links,[
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/background/opacity2.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/progress/progresscircle.gif',
			'http://www.napyfab.com/ajax-indicators/images/indicator_medium.gif',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/progress/progressbar.gif',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/feed/feed-icon-14x14.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--javascript/yahooui/assets/sprite.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/editor/icons1.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/editor/icons3.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/shade2_n.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--images/navibar/logo20.png',
			'http://d3g0gp89917ko0.cloudfront.net/v--b23e476b7ade/common--theme/base/images/cool-button-gradient.png'
		]);
	}

	public function test_parseCSSLinks2() {
		$text = file_get_contents( __DIR__ . '/files/003.css');
		$styleParser = new StyleParser();
		$links = $styleParser->parseCSSLinks('http://domain.wdfiles.com/local--files/component:theme/font-bauhaus.css', $text);
		$this->assertEquals($links,[
			'http://domain.wdfiles.com/local--files/component:theme/itc-bauhaus-lt-demi.eot',
			'http://domain.wdfiles.com/local--files/component:theme/itc-bauhaus-lt-demi.ttf'
		]);
	}

	public function test_mapFileToLink() {
		$text = file_get_contents( __DIR__ . '/files/003.css');
		$styleParser = new StyleParser();
		$links = $styleParser->parseCSSLinks('http://domain.wdfiles.com/local--files/component:theme/font-bauhaus.css', $text); 
		$file = 'files/test003.txt';
		$text2 = 'url("/local--files/component:theme/itc-bauhaus-lt-demi.eot")';
		$styleParser->mapFileToLink(
			'http://domain.wdfiles.com/local--files/component:theme/itc-bauhaus-lt-demi.ttf',
			$file
		);
		$this->assertEquals(
			'url("http://domain.wdfiles.com/local--files/component:theme/itc-bauhaus-lt-demi.eot")',
			$styleParser->replaceLinksInText($text2)
		);
	}
}