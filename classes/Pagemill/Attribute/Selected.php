<?php
class Pagemill_Attribute_Selected extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		if (!is_a($tag, 'Pagemill_Tag_Html_Select')) {
			throw new Exception("The {$name} attribute is only valid on select elements " . get_class($tag));
		}
		parent::__construct($name, $value, $tag);
		$tag->attachPreprocess(new Pagemill_TagPreprocessor_SetSelectValue($value));
		$selectvalue = new Pagemill_TagPreprocessor_SelectValue($tag);
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
