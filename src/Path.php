<?php
namespace aphp\Parser;

abstract class PathH {
	abstract public function absPath();
	abstract public function domainPath();
	abstract public function relativeToAbsolute($relativeUrl);
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
	const APHP_URL_SCHEME = 0;
	const APHP_URL_DOMAIN = 1;
	const APHP_URL_PATH = 2;
	const APHP_URL_QUERY = 3;
	const APHP_URL_FRAGMENT = 4;
	const APHP_URL_ISDIR = 5;

	public $components;
	public $url;

	public function __construct($url) {
		$this->url = $url;
		$this->parseUrl();
	}

	public function absPath() {
		$components[] = $this->components[ self::APHP_URL_SCHEME ];
		$components[] = $this->components[ self::APHP_URL_DOMAIN ];
		$components[] = $this->components[ self::APHP_URL_PATH ];
		for ($i = count($components) - 1; $i>=0; $i--) {
			if ($components[$i] == NULL) {
				unset($components[$i]);
			}
		}
		$absPath = trim(implode('/', $components), '/');
		if ($this->components[ self::APHP_URL_ISDIR ]) {
			return $absPath . '/';
		}
		$absPathItems = explode('/', $absPath);
		array_pop($absPathItems);
		return implode('/', $absPathItems) . '/';
	}

	public function domainPath() {
		$components[] = $this->components[ self::APHP_URL_SCHEME ];
		$components[] = $this->components[ self::APHP_URL_DOMAIN ];
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
			return implode('/', $components);
		}
		return $result;
	}

	// PROTECTED

	protected function parseUrl() {
		$components = parse_url($this->url);
		if (!is_array($components) || empty($components['scheme']) || empty($components['host'])) {
			throw Path_Exception::invalidUrl($this->url);
		}
		$this->components = [
			self::APHP_URL_SCHEME => $components['scheme'] . ':/',
			self::APHP_URL_DOMAIN => $components['host'] . (empty($components['port']) ? '' : ':' . $components['port']),
			self::APHP_URL_PATH => (empty($components['path']) || $components['path'] == '/') ? NULL : trim($components['path'], '/'),
			self::APHP_URL_QUERY => empty($components['query']) ? NULL : '?' . $components['query'],
			self::APHP_URL_FRAGMENT => empty($components['fragment']) ? NULL : '#' . $components['fragment'],
			self::APHP_URL_ISDIR => preg_match('#/$#', $this->url)
		];
		if (!$this->components[ self::APHP_URL_PATH ] && !$this->components[ self::APHP_URL_ISDIR ]) {
			$this->components[ self::APHP_URL_ISDIR ] = true;
			$this->url .= '/';
		}
	}
}