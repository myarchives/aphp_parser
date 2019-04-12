<?php 

use aphp\Parser\Browser;
use aphp\Parser\Bot;

class mockBrowser extends Browser {
	public $_nextResult = true;

	public function navigate ( $url ) {
		return $this->_nextResult;
	}

	public function downloadImage ( $url ) {
		return $this->_nextResult;
	}

	public function downloadFile ( $url ) {
		return $this->_nextResult;
	}
}

class mockBot extends Bot {
	public function addBrowser($userAgent) {
		$browser = new mockBrowser($userAgent, $this->tempDir, $this->prefix . count($this->browsers) . '_');
		if ($this->logger) 
			$browser->setLogger($this->logger);
		$this->browsers[] = $browser;
		$this->currentBrowser = $this->browsers[0];
		return $browser;
	}
	public function add_proxy_http($proxy, $userAgent) {
		$this->addBrowser($userAgent);
	}
}

