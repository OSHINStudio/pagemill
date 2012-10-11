<?php

class Pagemill_Tag_AlwaysExpand extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->collapse = false;
	}
}
