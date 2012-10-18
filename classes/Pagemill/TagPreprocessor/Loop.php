<?php
class Pagemill_TagPreprocessor_Loop extends Pagemill_TagPreprocessor {
	private $_loopAttributes;
	public function __construct($loopAttributes) {
		$this->_loopAttributes = $loopAttributes;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$attributes = array();
		$name = $this->_loopAttributes;
		$parts = explode(' ', $name, 2);
		$attributes['name'] = $parts[0];
		if (isset($parts[1])) $attributes['as'] = $parts[1];
		$loop = new Pagemill_Tag_Loop('loop', $attributes, null, $tag->doctype());
		$parent = $tag->parent();
		$parent->appendChild($loop);
		$loop->appendChild($tag);
		$loop->process($data, $stream);
		$parent->appendChild($tag);
		$loop->detach();
		return false;
	}
}
