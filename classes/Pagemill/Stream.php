<?php

class Pagemill_Stream {
	private $_buffer = false;
	private $_content = '';
	public function __construct($buffer = false) {
		$this->_buffer = $buffer;
	}
	public function append($string) {
		if ($this->_buffer) {
			$this->_content .= $string;
		} else {
			echo $string;
		}
	}
	public function peek() {
		return $this->_content;
	}
	public function clean() {
		$this->_content = '';
	}
	public function flush() {
		echo $this->_content;
		$this->clean();
	}
}
