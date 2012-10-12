<?php

class Pagemill_Tag_Comment extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->append('<!--');
		foreach ($this->children() as $child) {
			$child->rawOutput($data, $stream);
		}
		$stream->append('-->');
	}
}
