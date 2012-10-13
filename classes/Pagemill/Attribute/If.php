<?php

class Pagemill_Attribute_If extends Pagemil_Attribute {
	public function tag() {
		$attributes = array();
		$attributes['expr'] = $this->value;
		$wrapper = new Pagemill_Tag_If('if', $attributes, null, $this->tag->doctype());
		$wrapper->appendChild($this->tag);
		return $wrapper;
	}
}
