<?php

class Pagemill_Tag_AttributeTag extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		if (is_null($this->parent())) {
			throw new Exception('Attribute element requires a parent');
		}
		$this->parent()->attachPreprocess(new Pagemill_TagPreprocessor_AttributeTag($this));
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		// No output for typical processing. Content should be passed to an
		// attribute through the parent's preprocessor using
		// outputForAttribute() instead.
		return;
	}
	public function outputForAttribute(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			$child->output($data, $stream);
		}
	}
}
