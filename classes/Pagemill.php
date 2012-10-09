<?php

class Pagemill {
	private $_data;
	public function __construct(Pagemill_DataNode $data = null) {
		$this->_data = (is_null($data) ? (new Pagemill_DataNode()) : $data);
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
	public function parseFile() {
	
	}
	public function parseString() {
	
	}
	public function writeFile() {
	
	}
	public function writeString($source) {
		$parser = new Pagemill_Parser();
		$output = $parser->parse($source);
		return $output;
	}
}
