<?php 

namespace aphp\Parser;

class Config {
/*
http://proxy-daily.com/?PageSpeed=noscript
https://www.proxy-list.download/api/v1/get?type=https&anon=elite&country=US
https://www.proxy-list.download/api/v1/get?type=https&anon=elite
https://www.proxy-list.download/api/v1/get?type=https&anon=anonymous
https://webanetlabs.net/publ/24
*/
	public $proxyURL = 'https://www.proxy-list.download/api/v1/get?type=https&anon=anonymous';
/*
95.37.200.11:3128
46.17.99.4:8080
159.65.120.133:3128
142.93.132.238:8080
178.63.127.122:3128
*/
	public $proxyURLs_text = ''; // text of proxy URLs, manual or automatic set

	public $retryCount = 10;
	public $sleepTimeout = 3;
	public $retryCount_CSSResources = 4;

	public $userAgentList = __DIR__ . '/../textFiles/useragents.txt'; // file
}