<?php 

namespace aphp\Parser;

class BaseException extends \RuntimeException {
	public static function createException( /* ... */ ) {
		$args = func_get_args();
		$text = $args[0];
		unset($args[0]);
		return new static(sprintf($text, ...$args)); // PHP 5.6+
	}
}

// ----

class ResolveURL_Exception extends BaseException {
	public static function urlException($url) {
		return self::createException('url %s', $value);
	}
}
