<?php

class Pagemill_Attribute_Content extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$tag->attachPreprocess(new Pagemill_TagPreprocessor_ContentReplace($value, false));
	}
}
