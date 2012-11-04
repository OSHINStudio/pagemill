<?php

class Pagemill_TagPreprocessor_SelectValue extends Pagemill_TagPreprocessor {
	private $_selectTag;
	public function __construct(Pagemill_Tag $selectTag) {
		$this->_selectTag = $selectTag;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		if ($tag->hasAttribute('value')) {
			$selected = $this->_selectTag->selectedValue();
			$value = $data->parseVariables($tag->getAttribute('value'));
			if ($selected == $value) {
				$tag->setAttribute('selected', 'selected');
			} else {
				$tag->removeAttribute('selected');
			}
		}
	}
}
