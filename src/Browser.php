<?php

namespace aphp\Parser;

abstract class BrowserH {
	public $client = null; // HttpClient
	public $prefix = '';
	protected $rawdata = null; // raw data returned by the last query
	protected $tempFileExt = null;
	protected $tempFileMime = null;

	protected $cookieFile = 'cookies.txt';
	protected $tempFile = 'download.bin';

	abstract public function navigate ( $url );
	abstract public function downloadImage( $url );
	abstract public function downloadFile( $url );

	abstract public function getData(); // null OR string
	abstract public function getImageFileExt(); // png, jpg, gif, svg

	abstract public function getTempFileMime(); // mime string
	abstract public function getTempFileName();

	abstract public function isNavigateSucceed();
	abstract public function isDownloadSucceed();
}

// ------------------------
// Browser
// ------------------------

class Browser extends BrowserH {
	use \Psr\Log\LoggerAwareTrait; // trait

	// PROTECTED

	protected function resolveUrl ( $url ) {
		$url = trim ( $url );

		// Absolute URLs are fine
		if ( strpos ( $url, 'http' ) === 0 ) {
			return $url;
		}

		// Empty URLs represent current URL
		if ( $url === '' ) {
			return $this->client->get_effective_url();
		}

		/**
		 * If the URL begins with a forwards slash, it is absolute based on the current hostname
		 */
		$effective_url = $this->client->get_effective_url();
		if ( $url[0] === '/' ) {
			$port = ':' . parse_url ( $effective_url, PHP_URL_PORT );
			return parse_url ( $effective_url, PHP_URL_SCHEME ) . '://' . parse_url ( $effective_url, PHP_URL_HOST ) . ( $port !== ':' ? $port : '' ) . $url;
		}
		
		throw ResolveURL_Exception::urlException($url);
	}

	// Override
	public function setLogger(\Psr\Log\LoggerInterface $logger) {
		$this->logger = $logger;
		$this->client->setLogger($logger);
	}

	// PUBLIC

	public function __construct( $userAgent, $tempDir, $prefix = 'browser_' ) {
		$this->client = new HttpClient();
		$this->client->set_user_agent( $userAgent );

		$this->prefix = $prefix;
		$this->cookieFile = $tempDir . '/' . $prefix . $this->cookieFile;
		$this->tempFile = $tempDir . '/' . $prefix . $this->tempFile;

		$this->client->store_cookies( $this->cookieFile );
	}

	public function navigate ( $url ) {
		$newurl = $this->resolveUrl ( $url );
		if ($this->logger) {
			$this->logger->info("NAV : $newurl");
		}
		$this->rawdata = $this->client->fetch_get ( $newurl );
		if ($this->isNavigateSucceed()) {
			if ($this->logger) {
				$this->logger->info("200 : " . $this->client->last_url);
			}
		} else {
			if ($this->logger) {
				$this->logger->info("ERR : " . $this->client->last_url);
			}
		}
		return $this->isNavigateSucceed();
	}

	public function downloadImage( $url ) {
		$this->downloadFile($url);
		if ($this->isDownloadSucceed()) {
			$mime = $this->tempFileMime;
			if (strpos($mime, 'png') !== false) {
				$this->tempFileExt = '.png';
			}
			elseif (strpos($mime, 'jpeg') !== false) {
				$this->tempFileExt = '.jpg';
			}
			elseif (strpos($mime, 'gif') !== false) {
				$this->tempFileExt = '.gif';
			}
			elseif (strpos($mime, 'svg') !== false) {
				$this->tempFileExt = '.svg';
			}
		}
		return $this->isDownloadSucceed();
	}

	public function downloadFile( $url ) {
		$newurl = $this->resolveUrl ( $url );
		if ($this->logger) {
			$this->logger->info("DW  : $newurl");
		}
		$fp = fopen($this->tempFile, 'w');
		if ( $this->client->fetch_file ( $newurl, $fp ) ) {
			fclose($fp);
			$this->tempFileMime = mime_content_type($this->tempFile);
		} else {
			fclose($fp);
			$this->tempFileMime = null;
		}
		if ($this->isDownloadSucceed()) {
			if ($this->logger) {
				$this->logger->info("200 : " . $this->client->last_url);
			}
		} else {
			if ($this->logger) {
				$this->logger->info("ERR : " . $this->client->last_url);
			}
		}
		return $this->isDownloadSucceed();
	}
	
	public function getData() {
		return $this->rawdata;
	}

	public function getImageFileExt() {
		return $this->tempFileExt;
	}

	public function getTempFileMime() {
		return $this->tempFileMime;
	}

	public function getTempFileName() {
		return $this->tempFile;
	}

	public function isNavigateSucceed() {
		return ($this->rawdata !== null);
	}

	public function isDownloadSucceed() {
		return ($this->tempFileMime !== null);
	}
}
