<?php
class Pagemill_Attribute {
	protected $name;
	protected $value;
	protected $tag;
	public function __construct($name, $value, $tag) {
		$this->name     = $name;
		$this->value    = $value;
		$this->tag      = $tag;
	}
	public function tag() {
		return $this->tag;
	}
}
