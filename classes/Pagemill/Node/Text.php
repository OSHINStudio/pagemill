<?php

class Pagemill_Node_Text extends Pagemill_Node {
	private $_text = '';
	public function appendChild(Pagemill_Node $node) {
		throw new Exception('appendChild is not implemented for text nodes');
	}
	public function appendText($text) {
		$this->_text .= (string)$text;
	}
	protected function output(Pagemill_Data $data, Pagemill_Stream $stream, $encode = true) {
		if ($encode) {
			$stream->append(htmlentities($data->parseVariables($this->_text), 0, 'UTF-8'));
		} else {
			$stream->append($data->parseVariables($this->_text));
		}
	}
	public function process(Pagemill_Data $data, Pagemill_Stream $stream, $encode = true) {
		$this->output($data, $stream, $encode);
	}
}
