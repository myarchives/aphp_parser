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
	public function add_proxy_http($proxy, $userAgent) {
		$this->addBrowser($userAgent);
	}
}

