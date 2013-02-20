<?php
class Pagemill_Attribute_Loop extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$attributes = array();
		$parts = explode(' ', $value, 2);
		$attributes['name'] = $parts[0];
		if (isset($parts[1])) $attributes['as'] = $parts[1];
		$loop = new Pagemill_Tag_Loop('loop', $attributes, null, $tag->doctype());
		if ($tag->parent()) $tag->parent()->appendChild($loop);
		$loop->appendChild($tag);
	}
}
