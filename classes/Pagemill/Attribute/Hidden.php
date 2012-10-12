<?php
/**
 * Attributes of this class remove themselves from the parent tag.
 */
class Pagemill_Attribute_Hidden extends Pagemill_Attribute {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$this->tag->removeAttribute($name);
	}
}
