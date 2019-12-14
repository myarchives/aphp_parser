<?php
use aphp\Foundation\ANullObject;

class MockClient extends ANullObject
{
	public $_get_http_response_code = 200;
	public $_fetch_get = '';
	public $_fetch_post = '';

	public function get_http_response_code() {
		return $this->_get_http_response_code;
	}
	public function fetch_get($url) {
		return $this->_fetch_get;
	}
	public function fetch_post($url) {
		return $this->_fetch_post;
	}
}