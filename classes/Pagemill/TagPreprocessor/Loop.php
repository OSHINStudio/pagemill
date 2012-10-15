<?php
class Pagemill_TagPreprocessor_Loop extends Pagemill_TagPreprocessor {
	private $_loopAttributes;
	public function __construct($loopAttributes) {
		$this->_loopAttributes = $loopAttributes;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$attributes = array();
		$attributes['name'] = $this->_loopAttributes;
		$loop = new Pagemill_Tag_Loop('loop', $attributes, null, $tag->doctype());
		$loop->appendChild($tag);
		$loop->process($data, $stream);
		return false;
	}
}
