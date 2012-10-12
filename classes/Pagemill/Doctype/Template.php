<?php
class Pagemill_Doctype_Template extends Pagemill_Doctype {
	public function __construct($nsPrefix) {
		parent::__construct($nsPrefix);
		
		$this->keepNamespaceDeclarationInOutput = false;
		
		$this->registerTag('template', 'Pagemill_Tag_Template');
		$this->registerTag('attribute', 'Pagemill_Tag_AttributeTag');
		$this->registerTag('loop', 'Pagemill_Tag_Loop');
		$this->registerTag('for-each', 'Pagemill_Tag_Loop');
		$this->registerTag('if', 'Pagemill_Tag_If');
		$this->registerTag('else', 'Pagemill_Tag_Else');
		$this->registerTag('select', 'Pagemill_Tag_Select');	// TODO: Deprecate
		$this->registerTag('/option', 'Pagemill_Tag_Option');	// TODO: Deprecate
		$this->registerTag('choose', 'Pagemill_Tag_Choose');
		$this->registerTag('include', 'Pagemill_Tag_Include');
		
		$this->registerAttribute('loop', 'Pagemill_Attribute_Loop');
		$this->registerAttribute('for-each', 'Pagemill_Attribute_Loop');
		$this->registerAttribute('checked', 'Pagemill_Attribute_Checked');
		$this->registerAttribute('selected', 'Pagemill_Attribute_Selected');
	}
}
