<?php 

require __DIR__ . '/../../vendor/autoload.php';

use aphp\Router\Router;
use aphp\Parser\StyleParser;

$router = new Router();

$port = $_SERVER['SERVER_PORT'];
$styleParser = new StyleParser();
$links = $styleParser->parseCSSLinks("http://localhost:$port/v--b23e476b7ade/common--theme/base/css/style.css", file_get_contents(__DIR__ . '/style001.css'));
//$links = $styleParser->parseCSSLinks("http://localhost:$port/style.css", file_get_contents(__DIR__ . '/style001.css'));

$router->set404(function() use ($router, $links) {
	header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
	echo '404 ' . $router->request->getCurrentUri();
	echo PHP_EOL . '<pre>';
	print_r($links);
});

$router->get('/', function() {
	echo 
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
<head>
    <title></title>
    <meta http-equiv="content-type" content="text/html;charset=UTF-8"/>
    <meta http-equiv="content-language" content="ru"/>
    <style type="text/css" id="internal-style">
        /* modules */
		@import url(/v--b23e476b7ade/common--modules/css/wiki/pagestagcloud/PagesTagCloudModule.css);  
        /* theme */
        @import url(/v--b23e476b7ade/common--theme/base/css/style.css);
	</style>
</head>
<body id="html-body">
	body text
</body>
</html>';
});

$router->get('/v--b23e476b7ade/common--modules/css/wiki/pagestagcloud/PagesTagCloudModule.css', function() {
	echo 'div.pages-tag-cloud-box {
		/*border: 1px solid #BBD;*/
		padding: 0 1px;
		/*font-size: 3em;*/
		overflow: hidden;
	
	}
	div.pages-tag-cloud-box a.tag{
		text-decoration: none;
		padding: 0 0.3em;
	}';
});

$router->get('/v--b23e476b7ade/common--theme/base/css/style.css', function() {
	echo file_get_contents(__DIR__ . '/style001.css');
});

foreach ($links as $link) {
	$link = substr($link, strlen("http://localhost:$port"));
	$router->get(preg_quote($link), function() use($link) {
		echo 'success ' . $link;
	});
}

$router->run();