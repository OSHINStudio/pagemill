<?php

class Pagemill_Tag_Option extends Pagemill_Tag {
	private function _getSelect() {
		static $select = null;
		if (is_null($select)) {
			$select = $this->parent;
			while (!is_null($select)) {
				if (get_class($select) == 'Pagemill_Tag_Select') {
					return $select;
				}
				$select = $select->parent;
			}
		}
		return $select;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$select = $this->_getSelect();
		if (get_class($select) == 'Pagemill_Tag_Select') {
			$selectedValue = $select->selectedValue();
			$value = $data->parseVariables($this->getAttribute('value'));
			if ($selectedValue == $value) {
				$this->setAttribute('selected', 'selected');
			} else {
				$this->removeAttribute('selected');
			}
		}
		parent::output($data, $stream);
	}
}
