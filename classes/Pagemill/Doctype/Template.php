<?php
class Pagemill_Doctype_Template extends Pagemill_Doctype {
	public function __construct($nsPrefix) {
		parent::__construct($nsPrefix);
		$this->registerTag('attribute', 'Pagemill_Tag_Attribute');
		$this->registerTag('loop', 'Pagemill_Tag_Loop');
		$this->registerTag('if', 'Pagemill_Tag_If');
		$this->registerTag('else', 'Pagemill_Tag_Else');
	}
}
