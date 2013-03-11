<?php
class Pagemill_Doctype_Template extends Pagemill_Doctype {
	public function __construct($nsPrefix) {
		$this->nsUri = 'http://typeframe.com/pagemill';
		parent::__construct($nsPrefix);
		$this->keepNamespaceDeclarationInOutput = false;
		
		$this->registerTag('template', 'Pagemill_Tag_Template');
		$this->registerTag('attribute', 'Pagemill_Tag_AttributeTag');
		$this->registerTag('loop', 'Pagemill_Tag_Loop');
		$this->registerTag('for-each', 'Pagemill_Tag_Loop');
		$this->registerTag('if', 'Pagemill_Tag_If');
		$this->registerTag('else', 'Pagemill_Tag_Else');
		$this->registerTag('choose', 'Pagemill_Tag_Choose');
		$this->registerTag('include', 'Pagemill_Tag_Include');
		$this->registerTag('recurse', 'Pagemill_Tag_Recurse');
		$this->registerTag('eval', 'Pagemill_Tag_Eval');
		
		$this->registerAttribute('loop', 'Pagemill_Attribute_Loop');
		$this->registerAttribute('for-each', 'Pagemill_Attribute_Loop');
		$this->registerAttribute('checked', 'Pagemill_Attribute_Checked');
		$this->registerAttribute('selected', 'Pagemill_Attribute_Selected');
		$this->registerAttribute('if', 'Pagemill_Attribute_If');
	}
}
