<?php

class Pagemill_Tag_Comment extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->puts('<!--');
		foreach ($this->children() as $child) {
			$child->rawOutput($stream, false);
		}
		$stream->puts('-->');
	}
}
