<?php
class Pagemill_Attribute implements Pagemill_AttributeInterface {
	protected $name;
	protected $value;
	protected $tag;
	public function __construct($name, $value, Pagemill_Tag $tag) {
		$this->name     = $name;
		$this->value    = $value;
		$this->tag      = $tag;
	}
}
