<?php

class Pagemill_TagPreprocessor_SetSelectValue extends Pagemill_TagPreprocessor {
	private $_selectedValue;
	public function __construct($selectedValue) {
		$this->_selectedValue = $selectedValue;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$tag->selectedValue($data->parseVariables($this->_selectedValue));
	}
}
