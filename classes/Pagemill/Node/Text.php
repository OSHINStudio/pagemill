<?php

class Pagemill_Node_Text extends Pagemill_Node {
	private $_text = '';
	public function appendChild(Pagemill_Node $node) {
		throw new Exception('Not implemented');
	}
	public function appendText($text) {
		$this->_text .= (string)$text;
	}
	protected function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->append($data->parseVariables($this->_text));
	}
	public function process(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->output($data, $stream);
	}
}
