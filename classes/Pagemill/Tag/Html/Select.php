<?php

class Pagemill_Tag_Html_Select extends Pagemill_Tag {
	private $_selectedValue;
	public function selectedValue() {
		$args = func_get_args();
		if (count($args)) {
			$this->_selectedValue = $args[0];
		}
		return $this->_selectedValue;
	}
}
