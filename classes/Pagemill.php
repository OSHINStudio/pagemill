<?php

class Pagemill {
	private $_data;
	private static $_processedTemplates = array();
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
	public function setArray(array $array) {
		$this->_data->setArray($array);
	}
	public function setVariable($key, $value) {
		$this->set($key, $value);
	}
	public function setVariableArray(array $array) {
		$this->setArray($array);
	}
	public function get($key) {
		return $this->_data->get($key);
	}
	public function getVariable($key) {
		return $this->get($key);
	}
	public function addLoop() {
		$args = func_get_args();
		if (count($args) < 2) {
			throw new Exception('Pagemill->addLoop() requires at least 2 arguments');
		}
		$loop =& $this->_data;
		while (count($args) > 1) {
			$key = array_shift($args);
			if (!isset($loop[$key])) {
				$loop[$key] = array();
				$loop =& $loop[$key];
			} else {
				if (!is_array($loop[$key])) {
					throw new Exception("Loops can only be added to arrays.");
				}
				$loop =& $loop[$key];
			}
		}
		$loop[] = $args[0];
	}
	public function sortLoop() {
		$args = func_get_args();
		$this->_data->sortNodes($args);
	}
	/**
	 * Parse a template file into a tag tree for processing.
	 * @param string $file The filename.
	 * @param Pagemill_Doctype $doctype The doctype. Inferred from file
	 * extension or source if null.
	 * @param boolean $useCache Read/write to cache if available.
	 * @return Pagemill_Tag
	 */
	public function parseFile($file, $doctype = null, $useCache = true) {
		self::$_processedTemplates[] = $file;
		if (is_null($file) || $file === '') {
			throw new Exception('File name required');
		}
		if (!file_exists($file)) {
			throw new Exception("File '{$file}' does not exist");
		}
		if (is_dir($file)) {
			throw new Exception("'{$file}' is a directory");
		}
		if (defined('PAGEMILL_CACHE_DIR') && $useCache) {
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
		if (is_null($doctype)) {
			$doctype = Pagemill_Doctype::ForFile($file);
		}
		$tree = $this->parseString($source, $doctype);
		if (defined('PAGEMILL_CACHE_DIR')) {
			$serial = serialize($tree);
			file_put_contents($cacheFile, $serial);
			@chmod($cacheFile, 0666);
		}
		return $tree;
	}
	/**
	 * Parse a template string into a tag tree for processing.
	 * @param string $source
	 * @param Pagemill_Doctype The doctype to use. Inferred from source if null.
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
	 * @param boolean $return If true, return the output as a string.
	 * @param boolean $useCache Read/write cache if available.
	 * @return string|null
	 */
	public function writeFile($file, $return = false, $useCache = true) {
		$stream = new Pagemill_Stream($return);
		$tree = $this->parseFile($file, null, $useCache);
		$tree->process($this->_data, $stream);
		return $stream->peek();
	}
	public static function ProcessedTemplates() {
		return self::$_processedTemplates;
	}
	/**
	 * Process a template string and send it to output.
	 * @param string $source
	 * @param boolean $return If true, return the output as a string.
	 */
	public function writeString($source, $return = false) {
		$stream = new Pagemill_Stream($return);
		$tree = $this->parseString($source);
		$tree->process($this->_data, $stream);
		return $stream->peek();
	}
}
