<?php 

namespace aphp\Parser;

abstract class StyleParserH {
	protected $path;
	protected $linkMap = [];
	abstract public function parseHTMLLinks($url, $text);
	abstract public function parseCSSLinks($url, $text);
	abstract public function mapFileToLink($link, $filePath);
	abstract public function replaceLinksInText($text);
}

class StyleParser extends StyleParserH {
	// @import url(http://domain/css/style.css);
	// <link rel="stylesheet" type="text/css" href="index_files/1.css">
	// url("http://mysite.example.com/mycursor.png")
	// url('http://mysite.example.com/mycursor.png')
	// url(http://mysite.example.com/mycursor.png)
	
	public function parseHTMLLinks($url, $text) {
		$this->path = new Path($url);
		$links = [];
		if (preg_match_all( $this->preg('#<link.+?rel=QstylesheetQ.+?href=Q(.+?)Q.*?>#i'), $text, $m)) {
			$links = array_merge($links, $m[1]);
		}
		if (preg_match_all( $this->preg('#@import\s+url\(Q?(.+?)Q?\)#'), $text, $m)) {
			$links = array_merge($links, $m[1]);
		}
		return $this->linksMapAndReturn($links);
	}

	public function parseCSSLinks($url, $text) {
		$this->path = new Path($url);
		$links = [];
		if (preg_match_all( $this->preg('#url\(Q?([^;]+?)Q?\)#'), $text, $m)) {
			$links = array_merge($links, $m[1]);
		}
		return $this->linksMapAndReturn($links);
	}

	public function mapFileToLink($link, $filePath) {
		if (isset($this->linkMap[ $link ])) {
			$this->linkMap[ $filePath ] = $this->linkMap[ $link ];
			unset($this->linkMap[ $link ]);
		}
	}

	public function replaceLinksInText($text) {
		foreach($this->linkMap as $key=>$value) {
			$text = str_replace($value, $key, $text);
		}
		return $text;
	}

	// PROTECTED

	protected function preg($reg) {
		return str_replace([ 'Q' ], ['[\'"]'], $reg);
	}

	protected function linksMapAndReturn($links) {
		$links = array_unique($links);
		$links = array_values($links);
		$links_path = array_map( [ $this->path, 'relativeToAbsolute'], $links);

		for ($i = 0; $i < count($links_path); $i++) {
			if ($links_path[$i]) {
				$this->linkMap[ $links_path[$i] ] = $links[$i];
			}
		}
		return $links_path;
	}
}