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
			$stream->puts($this->doctype->encodeEntities($data->parseVariables($this->_text)));
		} else {
			$stream->puts($data->parseVariables($this->_text));
		}
	}
	protected function rawOutput(Pagemill_Stream $stream, $encode = true) {
		if ($encode) {
			$stream->puts($this->doctype->encodeEntities($this->_text));
		} else {
			$stream->puts($this->_text);
		}
	}
	public function process(Pagemill_Data $data, Pagemill_Stream $stream, $encode = true) {
		$this->output($data, $stream, $encode);
	}
	public function children() {
		return array();
	}
}
