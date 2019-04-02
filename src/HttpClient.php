<?php

namespace aphp\Parser;

abstract class HttpClientH {
	public $ch;
	public $last_url;
	public $urlTimeout = 30;
	public $fileTimeout = 60;
	protected $stdout = null;

	abstract public function set_referrer($referrer_url);
	abstract public function set_user_agent($useragent);
	abstract public function set_headers($headers);
	abstract public function enable_headers($trueOrFalse);
	abstract public function set_url($url);
	abstract public function store_cookies($cookie_file);
	abstract public function set_cookie($cookie);

	abstract public function get_effective_url();
	abstract public function get_http_response_code();
	abstract public function get_error_msg();

	abstract public function set_proxy_http($proxy);
	abstract public function set_proxy_socks4($proxy);

	abstract public function fetch_post($url, $postdata);
	abstract public function fetch_get($url);
	abstract public function fetch_file($url, $fp);

	abstract public function close();
}

// ------------------------
// HttpClient
// ------------------------

class HttpClient extends HttpClientH {
	use \Psr\Log\LoggerAwareTrait; // trait

	// PROTECTED

	protected function exec_data() {
		// return into a publiciable rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $this->stdout);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($this->ch, CURLOPT_VERBOSE, false);
		curl_setopt($this->ch, CURLOPT_HEADER, false);
		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->urlTimeout);
		$response = curl_exec($this->ch);
		//$data = substr($response, $header_size );
		//$header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
		//$header = substr($response, 0, $header_size);
		//$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if (curl_errno($this->ch)) {
			if ($this->logger) {
				$this->logger->info( $this->get_error_msg() );
			}
			return null;
		}
		return $response;
	}

	protected function exec_binary() {
		curl_setopt($this->ch, CURLOPT_VERBOSE, false);
		curl_setopt($this->ch, CURLOPT_HEADER, false);
		//set curl function timeout to $timeout
		curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->fileTimeout);
		curl_exec($this->ch);
		if (curl_errno($this->ch)) {
			if ($this->logger) {
				$this->logger->info( $this->get_error_msg() );
			}
			return false;
		}
		return true;
	}

	// PUBLIC

	public function __construct() {
		$this->ch = curl_init();
		$this->stdout = fopen('php://stdout','w');
		//set error in case http return code bigger than 300
		curl_setopt($this->ch, CURLOPT_FAILONERROR, true);
		// use gzip if possible
		curl_setopt($this->ch, CURLOPT_ENCODING , 'gzip, deflate');
		// enable redirects
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, true);
		// max redirs
		curl_setopt($this->ch, CURLOPT_MAXREDIRS, 20);
		// do not veryfy ssl
		// this is important for windows
		// as well for being able to access pages with non valid cert
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
	}

	public function set_referrer($referrer_url) {
		curl_setopt($this->ch, CURLOPT_REFERER, $referrer_url);
	}

	public function set_user_agent($useragent)	{
		curl_setopt($this->ch, CURLOPT_USERAGENT, $useragent);
	}

	public function set_headers($headers)	{
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
	}

	public function enable_headers($trueOrFalse) {
		curl_setopt($this->ch, CURLOPT_HEADER, $trueOrFalse);
	}

	public function set_url($url) {
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$this->last_url = $url;
	}

	public function store_cookies($cookie_file) {
		// use cookies on each request (cookies stored in $cookie_file)
		curl_setopt ($this->ch, CURLOPT_COOKIEJAR, $cookie_file);
		curl_setopt ($this->ch, CURLOPT_COOKIEFILE, $cookie_file);
	}
	
	public function set_cookie($cookie) { // fruit=apple; colour=red
		curl_setopt ($this->ch, CURLOPT_COOKIE, $cookie);
	}

	public function get_effective_url()	{
		return curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
	}

	public function get_http_response_code() {
		return curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
	}

	public function get_error_msg() {
		$err = "Error message: (" . curl_errno($this->ch) . ") " . curl_error($this->ch);
		return $err;
	}

	// PROXY

	public function set_proxy_http($proxy) { // '127.0.0.1:8888'
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
		curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
	}

	public function set_proxy_socks4($proxy) {
		curl_setopt($this->ch, CURLOPT_PROXY, $proxy);
		curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
	}

	// SEND API

	public function fetch_post($url, $postdata) {
		$this->set_url($url);
		curl_setopt($this->ch, CURLOPT_POST, true);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postdata);
		return $this->exec_data();
	}

	public function fetch_get($url) {
		$this->set_url($url);
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		return $this->exec_data();
	}

	public function fetch_file($url, $fp) {
		$this->set_url($url);
		curl_setopt($this->ch, CURLOPT_HTTPGET, true);
		// store data into file rather than displaying it
		curl_setopt($this->ch, CURLOPT_FILE, $fp);
		return $this->exec_binary();
	}

	public function close() {
		//close curl session and free up resources
		curl_close($this->ch);
	}

	/**
	 * This function allows curl to follow redirects without using CURLOPT_FOLLOWLOCATION
	 * which is disabled when using open_basedir or safe_mode
	 */
	/*
	protected $curl_loops = 0;
	protected $curl_max_loops = 20;

	protected function curl_exec_redir() {
		$ch = $this->ch;
		if ($this->curl_loops++ >= $this->curl_max_loops)
		{
			$this->curl_loops = 0;
			return null;
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		$result = [];
		$header = substr($response, 0, $header_size);
		$data = substr($response, $header_size );
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		$data = str_replace("\r", "\n", str_replace("\r\n", "\n", $data));
		
		if ( $http_code == 301 || $http_code == 302 )
		{
			// If we're redirected, we should revert to GET
			curl_setopt($ch, CURLOPT_HTTPGET, true);
			
			$matches = array();
			preg_match('/Location:\s*(.*?)(\n|$)/i', $header, $matches);
			$url = @parse_url(trim($matches[1]));

			if (!$url)
			{
				//couldn't process the url to redirect to
				$this->curl_loops = 0;
				return $data;
			}
			$last_url = parse_url(curl_getinfo($ch, CURLINFO_EFFECTIVE_URL));

			if (empty($url['scheme']))
				$url['scheme'] = $last_url['scheme'];
			if (empty($url['host']))
				$url['host'] = $last_url['host'];
			if (empty($url['port']) && !empty($last_url['port']))
				$url['port'] = $last_url['port'];

			$new_url = 
				$this->notEmpty($url, 'scheme') . '://' .
				$this->notEmpty($url, 'host') .
				(!empty($url['port']) ? ':' . $url['port'] : '' ) .
				$this->notEmpty($url, 'path') .
				(!empty($url['query']) ? '?' . $url['query'] : '' );
			if ($this->logger) {
				$this->logger->info("301: $new_url");
			}
			curl_setopt($ch, CURLOPT_URL, $new_url);
			$this->last_url = $new_url;
			return $this->curl_exec_redir();
		} else {
			$this->curl_loops=0;
			return $data;
		}
		
	}*/
	/*
	protected function notEmpty($arr, $key) {
		if (isset($arr[$key])) {
			return $arr[$key];
		}
		return '';
	}
	*/
}





