<?php
class Pagemill_Attribute_Selected extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		$selectvalue = new Pagemill_TagPreprocessor_SelectValue($value);
		$this->_attachToOptions($tag, $selectvalue);
	}
	private function _attachToOptions(Pagemill_Tag $parent, Pagemill_TagPreprocessor_SelectValue $selectvalue) {
		foreach ($parent->children() as $child) {
			if (is_a($child, 'Pagemill_Tag')) {
				if ($child->name() == 'option') {
					$child->attachPreprocess($selectvalue);
				} else if ($child->children()) {
					$this->_attachToOptions($child, $selectvalue);
				}
			}
		}
	}
}
