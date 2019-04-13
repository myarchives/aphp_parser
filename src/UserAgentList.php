<?php 
namespace aphp\Parser;

class UserAgentList {
	public $list = [];

	public function __construct($userAgentListFileName) {
		$this->list = explode("\n", file_get_contents($userAgentListFileName));
	}

	public $index = 0;
	public function getAgent() {
		return trim($this->list[ $this->index % count($this->list) ]);
		$this->index++;
	}
}

