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
	public function parseFile($file) {
		$source = file_get_contents($file);
		$tree = $this->parseString($source);
		return $tree;
	}
	public function parseString($source) {
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($source);
		return $tree;
	}
	public function writeFile($file) {
		$source = file_get_contents($file);
		return $this->writeString($source);
	}
	public function writeString($source) {
		$tree = $this->parseString($source);
		return $tree->process($this->_data, new Pagemill_Stream());
	}
}
