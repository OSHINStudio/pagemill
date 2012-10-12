<?php
class Pagemill_TagPreprocessor_AttributeTag extends Pagemill_TagPreprocessor {
	private $_attributeTag;
	public function __construct(Pagemill_Tag_AttributeTag $attributeTag) {
		$this->_attributeTag = $attributeTag;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data) {
		$stream = new Pagemill_Stream(true);
		$this->_attributeTag->outputForAttribute($data, $stream);
		$value = $stream->peek();
		if ($value !== '') {
			$tag->setAttribute($this->_attributeTag->getAttribute('name'), $value);
		}
	}
}
