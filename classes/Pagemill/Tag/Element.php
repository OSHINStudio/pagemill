<?php

class Pagemill_Tag_Element extends Pagemill_Tag {
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$name = $data->parseVariables($this->getAttribute('name'));
		if ($name) {
			$this->name = $name;
			parent::output($data, $stream);
		} else {
			foreach ($this->children() as $child) {
				$child->process($data, $stream);
			}
		}
	}
}
