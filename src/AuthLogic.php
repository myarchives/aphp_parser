<?php
namespace aphp\Parser;

// Closure logic class

class AuthLogic
{
	public $_isAuth = false;
	public $_bot = null; // Bot

	public function __construct()
	{
		$this->isAuth = function() {
			return $this->_isAuth;
		};
		$this->doAuth = function() {
			// logic
			$this->_isAuth = true;
			return true;
		};
		// Events
		$this->resetCookiesEvent = function() {
			$this->_isAuth = false;
		};
		$this->responseEvent = function() {
			$code = $this->_bot->client->get_http_response_code();
			if ($code >= 401 && $code < 500 && $code != 429) {
				sleep(1);
				return true;
			}
			return false;
		};
		$this->failEvent = function() {
			// logic
			return false;
		};
	}
}