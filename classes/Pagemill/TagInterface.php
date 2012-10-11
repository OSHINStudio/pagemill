<?php

interface Pagemill_TagInterface {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null);
}
