<?php

class Pagemill_Node_Frag extends Pagemill_Node {
	private $_text = '';
	public function appendChild(Pagemill_Node $node) {
		//throw new Exception('appendChild is not implemented for frag nodes');
		$tmp = new Pagemill_Stream(true);
		$node->rawOutput($tmp);
		$this->_text .= $tmp->peek();
	}
	public function appendText($text) {
		$this->_text .= $this->doctype->encodeEntities($text);
	}
	protected function output(Pagemill_Data $data, Pagemill_Stream $stream, $encode = true) {
		if ($encode) {
			$stream->puts($data->parseVariables($this->_text, $this->doctype));
		} else {
			$stream->puts($data->parseVariables($this->_text));
		}
	}
	protected function rawOutput(Pagemill_Stream $stream, $encode = true) {
		/*if ($encode) {
			$stream->puts($this->doctype->encodeEntities($this->_text));
		} else {
			$stream->puts($this->_text);
		}*/
		$stream->puts($this->_text);
	}
	public function process(Pagemill_Data $data, Pagemill_Stream $stream, $encode = true) {
		$this->output($data, $stream, $encode);
	}
	public function children() {
		return array();
	}
}
