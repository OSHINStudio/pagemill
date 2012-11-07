<?php

class Pagemill_TagPreprocessor_CheckValue extends Pagemill_TagPreprocessor {
	private $_checkvalue;
	public function __construct($checkvalue) {
		$this->_checkvalue = $checkvalue;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		if ($tag->hasAttribute('value')) {
			$checked = $data->parseVariables($this->_checkvalue);
			$value = $data->parseVariables($tag->getAttribute('value'));
			if ($checked == $value) {
				$tag->setAttribute('checked', 'checked');
			} else {
				$tag->removeAttribute('checked');
			}
		}
	}
}
