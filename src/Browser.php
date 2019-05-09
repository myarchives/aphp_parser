<?php
namespace aphp\Parser;
use aphp\Files\File;

abstract class BrowserH {
	public $client = null; // HttpClient
	public $prefix = '';
	public $proxyName = '';
	protected $rawdata = null; // raw data returned by the last query

	protected $cookieFile = 'cookies.txt';
	protected $tempFile = null; // aphp\Files\File
	protected $tempFileDownloaded = false;

	abstract public function navigate ( $url );
	abstract public function downloadFile( $url );

	abstract public function getData(); // null OR string

	// aphp\Files\File
	public function getTempFile()     { return $this->tempFile; }
	// aphp\Files\FilePath
	public function getTempFilePath() { return $this->tempFile->filepath(); }
	// string
	public function getTempFileName() { return $this->tempFile->filepath()->getPath(); }

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
		$url = trim ($url);
		// Absolute URLs are fine
		if (strpos( strtolower($url), 'http' ) === 0) {
			return $url;
		}
		// Empty URLs represent current URL
		if ($url === '') {
			return $this->client->get_effective_url();
		}

		$path = new Path($this->client->get_effective_url());
		$newUrl = $path->relativeToAbsolute($url);
		if (!$newUrl) {
			throw ResolveURL_Exception::urlException($url);
		}
		return $newUrl;
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
		$this->tempFile = new File($tempDir . '/' . $prefix . 'download.temp');

		$this->client->store_cookies( $this->cookieFile );
		$this->client->set_headers([
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8',
			'Accept-Language: en-US,en;q=0.5',
			'Connection: keep-alive',
			'Upgrade-Insecure-Requests: 1'
		]);
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

	public function downloadFile( $url ) {
		$newurl = $this->resolveUrl ( $url );
		if ($this->logger) {
			$this->logger->info("DW  : $newurl");
		}
		$this->tempFile->reset();
		$this->tempFileDownloaded = false;

		$fp = fopen($this->getTempFileName(), 'w');
		$this->tempFileDownloaded = (true == $this->client->fetch_file( $newurl, $fp ));
		fclose($fp);
		
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

	public function isNavigateSucceed() {
		return ($this->rawdata !== null);
	}

	public function isDownloadSucceed() {
		return $this->tempFileDownloaded;
	}
}
