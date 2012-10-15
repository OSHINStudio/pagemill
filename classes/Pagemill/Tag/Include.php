<?php

class Pagemill_Tag_Include extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$file = $data->parseVariables($this->getAttribute('file'));
		$pm = new Pagemill($data);
		$tree = $pm->parseFile($file);
		$tree->process($data, $stream);
	}
}
