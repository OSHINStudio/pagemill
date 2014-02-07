<?php
class Pagemill_TagPreprocessor_AttributeTag extends Pagemill_TagPreprocessor {
	private $_attributeTag;
	public function __construct(Pagemill_Tag_AttributeTag $attributeTag) {
		$this->_attributeTag = $attributeTag;
	}
	public function process(\Pagemill_Tag $tag, \Pagemill_Data $data, \Pagemill_Stream $stream) {
		$stream = new Pagemill_Stream(true);
		$this->_attributeTag->outputForAttribute($data, $stream);
		$value = $stream->peek();
		if ($value !== '') {
			$value = html_entity_decode($value);
			$tag->setAttribute($this->_attributeTag->getAttribute('name'), $value);
		}
	}
}
