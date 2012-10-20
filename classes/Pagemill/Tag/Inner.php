<?php
class Pagemill_Tag_Inner extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
	}
}
