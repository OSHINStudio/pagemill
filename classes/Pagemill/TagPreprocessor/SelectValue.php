<?php

class Pagemill_TagPreprocessor_SelectValue extends Pagemill_TagPreprocessor {
	private $_selectvalue;
	public function __construct($selectvalue) {
		$this->_selectvalue = $selectvalue;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		if ($tag->hasAttribute('value')) {
			$selected = $data->parseVariables($this->_selectvalue);
			$value = $data->parseVariables($tag->getAttribute('value'));
			if ($selected == $value) {
				$tag->setAttribute('selected', 'selected');
			} else {
				$tag->removeAttribute('selected');
			}
		}
	}
}
