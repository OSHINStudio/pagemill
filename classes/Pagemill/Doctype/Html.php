<?php

class Pagemill_Doctype_Html extends Pagemill_Doctype {
	public function __construct($nsPrefix) {
		$this->nsUri = 'http://www.w3.org/1999/xhtml';
		parent::__construct($nsPrefix);

		$this->registerTag('a', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('b', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('body', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('div', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('em', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('head', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('i', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('iframe', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('option', 'Pagemill_Tag_Html_Option');
		$this->registerTag('p', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('script', 'Pagemill_Tag_Html_Script');
		$this->registerTag('select', 'Pagemill_Tag_Html_Select');
		$this->registerTag('span', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('strong', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('textarea', 'Pagemill_Tag_AlwaysExpand');
		$this->registerTag('title', 'Pagemill_Tag_AlwaysExpand');

		$this->addEntityArray(get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8'));
	}
}
