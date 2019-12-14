<?php
namespace aphp\Parser;
use aphp\Files\File;
use aphp\HttpClient\Browser as ABrowser;
use aphp\HttpClient\BrowserConfig;

// ------------------------
// Browser
// ------------------------

class Browser extends ABrowser {
	public function __construct(BrowserConfig $config)
	{
		$this->fileClass = File::class;
		parent::__construct($config);
	}
	public function getTempFileName()
	{
		return $this->tempFile->filepath()->getPath();
	}
	public function getTempFile() // aphp\Files\File
	{
		return $this->tempFile;
	}
	public function getTempFilePath() // aphp\Files\FilePath
	{
		return $this->tempFile->filepath();
	}
}
