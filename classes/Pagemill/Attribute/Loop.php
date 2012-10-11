<?php
class Pagemill_Attribute_Loop extends Pagemill_Attribute_Hidden {
	public function tag() {
		list($loop, $as) = (is_int(strpos($this->value, ' ')) ?
			explode(' ', $this->value, 2) :
			array($this->value, false));
		// create a pm:loop wrapper around this element
		$attributes = array();
		$attributes['name'] = $loop;
		if ($as) $attributes['as'] = $as;
		$wrapper = new Pagemill_Tag_Loop('pm:loop', $attributes, null, $this->tag->doctype());
		$wrapper->appendChild($this->tag);
		return $wrapper;
	}
}
