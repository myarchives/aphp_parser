<?php
namespace aphp\Parser;

abstract class PathH {
	public $components;
	public $url;

	abstract public function filePath();
	abstract public function absPath();
	abstract public function domainPath();
	abstract public function relativeToAbsolute($relativeUrl);
	abstract public function isDomainUrl($url);

	// static function filteredUrl($url)
}

// ---------
// Path
// ---------

/* 
domain/path/ ~ (ISDIR = TRUE)
domain/path/file.css ~ (ISDIR = FALSE)
domain/path ~ (ISDIR = FALSE)
*/

class Path extends PathH {
	const URL_SCHEME = 0;
	const URL_DOMAIN = 1;
	const URL_PATH = 2;
	const URL_QUERY = 3;
	const URL_FRAGMENT = 4;
	const URL_ISDIR = 5;
	static $filteredUrlMaxLength = 35;

	// STATIC

	static function filteredUrl($url) {
		$url = preg_replace("#([^\w\s\d\.\-_\[\]\(\)]|[\.]{2,})#", '', strtolower($url));
		if (strlen($url) > self::$filteredUrlMaxLength) {
			$url = substr($url, strlen($url)-self::$filteredUrlMaxLength , self::$filteredUrlMaxLength );
		}
		return $url;
	}

	// PUBLIC

	public function __construct($url) {
		$this->url = $url;
		$this->parseUrl();
	}

	public function filePath() {
		$components[] = $this->components[ self::URL_SCHEME ];
		$components[] = $this->components[ self::URL_DOMAIN ];
		$components[] = $this->components[ self::URL_PATH ];
		for ($i = count($components) - 1; $i>=0; $i--) {
			if ($components[$i] == NULL) {
				unset($components[$i]);
			}
		}
		$absPath = trim(implode('/', $components), '/');
		if ($this->components[ self::URL_ISDIR ]) {
			return $absPath . '/';
		}
		return $absPath;
	}

	public function absPath() {
		$absPath = $this->filePath();
		if ($this->components[ self::URL_ISDIR ]) {
			return $absPath;
		}
		$absPathItems = explode('/', $absPath);
		array_pop($absPathItems);
		return implode('/', $absPathItems) . '/';
	}

	public function domainPath() {
		$components[] = $this->components[ self::URL_SCHEME ];
		$components[] = $this->components[ self::URL_DOMAIN ];
		return trim(implode('/', $components), '/') . '/';
	}

	public function relativeToAbsolute($relativeUrl) {
		$absPath = $this->absPath();
		// http
		if (preg_match('#^http#i', $relativeUrl)) {
			return $relativeUrl;
		}
		// ./path/path
		if (preg_match('#^\./#', $relativeUrl)) {
			return $absPath . substr($relativeUrl, 2);
		}
		// /path/path
		if (preg_match('#^/#', $relativeUrl)) {
			return $this->domainPath() . substr($relativeUrl, 1);
		}
		$result = $absPath . $relativeUrl;
		// ../path/../path
		if (preg_match('#\.\.#', $result)) {
			$components = explode('/', $result);
			$count = count($components);
			for ($i = 0; $i < $count; $i++) {
				if (!isset($components[$i])) {
					continue;
				}
				if ($components[$i] == '..') {
					unset($components[$i]);
					while($i>0) {
						$i--;
						if (isset($components[$i])) {
							unset($components[$i]);
							break;
						}
					}
				}
			}
			$result = implode('/', $components);
			if (strpos($result, $this->domainPath()) !== 0) {
				return null;
			}
		}
		return $result;
	}

	public function isDomainUrl($url) {
		return (strpos($url, trim($this->components[self::URL_DOMAIN],'/')) !== false);
	}

	// PROTECTED

	protected function parseUrl() {
		$components = parse_url($this->url);
		if (!is_array($components) || empty($components['scheme']) || empty($components['host'])) {
			throw Path_Exception::invalidUrl($this->url);
		}
		$this->components = [
			self::URL_SCHEME => $components['scheme'] . ':/',
			self::URL_DOMAIN => $components['host'] . (empty($components['port']) ? '' : ':' . $components['port']),
			self::URL_PATH => (empty($components['path']) || $components['path'] == '/') ? NULL : trim($components['path'], '/'),
			self::URL_QUERY => empty($components['query']) ? NULL : '?' . $components['query'],
			self::URL_FRAGMENT => empty($components['fragment']) ? NULL : '#' . $components['fragment'],
			self::URL_ISDIR => preg_match('#/$#', $this->url)
		];
		if (!$this->components[ self::URL_PATH ] && !$this->components[ self::URL_ISDIR ]) {
			$this->components[ self::URL_ISDIR ] = true;
			$this->url .= '/';
		}
	}
}




