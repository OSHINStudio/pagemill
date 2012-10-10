<?php
class Pagemill_Tag_Preprocess_Attribute extends Pagemill_Tag_Preprocess {
	private $_attributeTag;
	public function __construct(Pagemill_Tag_Attribute $attributeTag) {
		$this->_attributeTag = $attributeTag;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data) {
		$stream = new Pagemill_Stream(true);
		$this->_attributeTag->outputForAttribute($data, $stream);
		$value = $stream->peek();
		$tag->setAttribute($this->_attributeTag->getAttribute('name'), $value);
	}
}
