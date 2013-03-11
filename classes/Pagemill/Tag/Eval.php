<?php

class Pagemill_Tag_Eval extends Pagemill_Tag {
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$result = $data->evaluate($this->getAttribute('expr'));
		$stream->puts($result);
	}
}
