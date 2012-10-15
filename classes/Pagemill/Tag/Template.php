<?php

class Pagemill_Tag_Template extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
	}
}
