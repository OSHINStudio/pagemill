<?php

class Pagemill_Tag_Include extends Pagemill_Tag {
	private static $_includeCache = array();
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$file = $data->parseVariables($this->getAttribute('file'));
		if (!isset(self::$_includeCache[$file])) {
			$pm = new Pagemill($data);
			$tree = $pm->parseFile($file, $this->doctype());
			self::$_includeCache[$file] = $tree;
		} else {
			$tree = self::$_includeCache[$file];
		}
		$this->appendChild($tree);
		$tree->process($data, $stream);
		$tree->detach();
	}
}
