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
	public $userAgentList = __DIR__ . '/../textFiles/useragents.txt'; // file
	public $urlTimeout = 30;
	public $fileTimeout = 120;
	
	public function botSettings_default() {
		$settings = new BotSettings();
		//$settings->retryCount = 10;
		//$settings->sleepTimeout = 3;
		//$settings->maxProxyCount = 75;
		return $settings;
	}

	public function botSettings_css() {
		$settings = $this->botSettings_default();
		$settings->retryCount = 4;
		return $settings;
	}
}