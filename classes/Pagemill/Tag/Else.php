<?php
class Pagemill_Tag_Else extends Pagemill_Tag {
	/**
	 * @var Pagemill_Tag_If
	 */
	private $_lastIf;
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		if (!$this->parent()) {
			throw new Exception("Else tag requires parent");
		}
		$lastElement = null;
		foreach ($this->parent->children() as $child) {
			if ($child === $this) {
				break;
			}
			$lastElement = (get_class($child) == 'Pagemill_Tag_If' ? $child : null);
		}
		if (is_null($lastElement)) {
			throw new Exception('Nearest sibling to else must be an if');
		}
		$this->_lastIf = $lastElement;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		if (!$this->_lastIf->lastResult()) {
			foreach ($this->children() as $child) {
				$child->process($data, $stream);
			}
		}
	}
}
