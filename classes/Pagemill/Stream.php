<?php
/**
 * A simple stream class that can either store content in a buffer or send it
 * to stdout (i.e., echo).
 */
class Pagemill_Stream {
	private $_buffer = false;
	private $_content = '';
	/**
	 * @param boolean $buffer If true, content will be buffered until the
	 * stream is explicitly cleaned or flushed. If false, content will be
	 * sent directly to output.
	 */
	public function __construct($buffer = false) {
		$this->_buffer = $buffer;
	}
	/**
	 * Add a string to the stream.
	 * @param string $string
	 */
	public function puts($string) {
		if ($this->_buffer) {
			$this->_content .= $string;
		} else {
			echo $string;
		}
	}
	/**
	 * Get the current content in the buffer. This method will not send the
	 * content to output.
	 * @return string
	 */
	public function peek() {
		return $this->_content;
	}
	/**
	 * Empty the buffer and return its most recent content. This method will not
	 * send the content to output.
	 * @return string
	 */
	public function clean() {
		$tmp = $this->_content;
		$this->_content = '';
		return $tmp;
	}
	/**
	 * Send the content of the buffer to output.
	 */
	public function flush() {
		echo $this->_content;
		$this->clean();
	}
}
