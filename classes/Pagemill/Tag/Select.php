<?php
// TODO: Deprecate this tag in favor of the pm:selected attribute.
class Pagemill_Tag_Select extends Pagemill_Tag {
	private $_selected;
	public function selectedValue() {
		return $this->_selected;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->_selected = $data->parseVariables($this->getAttribute('selected'));
		$this->removeAttribute('selected');
		$this->name = 'select';
		parent::output($data, $stream);
	}
}
