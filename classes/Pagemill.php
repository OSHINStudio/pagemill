<?php

class Pagemill {
	private $_data;
	public function __construct(Pagemill_Data $data = null) {
		$this->_data = (is_null($data) ? (new Pagemill_Data()) : $data);
	}
	public function root() {
		return $this->_data;
	}
	public function data() {
		return $this->_data;
	}
	public function set($key, $value) {
		$this->_data->set($key, $value);
	}
	public function setVariable($key, $value) {
		$this->set($key, $value);
	}
	public function get($key) {
		return $this->_data->get($key);
	}
	public function getVariable($key) {
		return $this->get($key);
	}
	public function addLoop() {
	
	}
	public function sortLoop() {
	
	}
	/**
	 * Parse a template file into a tag tree for processing.
	 * @param string $file The filename.
	 * @return Pagemill_Tag
	 */
	public function parseFile($file) {
		if (defined('PAGEMILL_CACHE_DIR')) {
			$md5 = md5($file);
			$cacheFile = PAGEMILL_CACHE_DIR . "/{$md5}";
			if (file_exists($cacheFile)) {
				$cacheTime = filemtime($cacheFile);
				$tmplTime = filemtime($file);
				if ($tmplTime < $cacheTime) {
					$serial = file_get_contents($cacheFile);
					return unserialize($serial);
				}
			}
		}
		$source = file_get_contents($file);
		$doctype = Pagemill_Doctype::ForFile($file);
		$tree = $this->parseString($source, $doctype);
		if (defined('PAGEMILL_CACHE_DIR')) {
			$serial = serialize($tree);
			file_put_contents($cacheFile, $serial);
		}
		return $tree;
	}
	/**
	 * Parse a template string into a tag tree for processing.
	 * @param string $source
	 * @return Pagemill_Tag
	 */
	public function parseString($source, Pagemill_Doctype $doctype = null) {
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($source, $doctype);
		return $tree;
	}
	/**
	 * Process a template file and send it to output.
	 * @param string $file The filename.
	 */
	public function writeFile($file) {
		$tree = $this->parseFile($file);
		return $tree->process($this->_data, new Pagemill_Stream());
	}
	/**
	 * Process a template string and send it to output.
	 * @param string $source
	 */
	public function writeString($source, Pagemill_Doctype $doctype = null) {
		$tree = $this->parseString($source);
		return $tree->process($this->_data, new Pagemill_Stream());
	}
}
