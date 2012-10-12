<?php

class Pagemill_Tag_Include extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Data $stream) {
		$file = $data->parseVariables($this->getAttribute('file'));
		$pm = new Pagemill($data);
		$tree = $pm->parseFile($data);
		$tree->process($data, $stream);
	}
}
