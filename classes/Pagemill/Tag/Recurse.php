<?php
class Pagemill_Tag_Recurse extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$head = $data->parseVariables($this->getAttribute('head'));
		if (!$head) {
			throw new Exception("Recurse tag requires a head attribute");
		}
		$hasLoop = false;
		$parent = $this->parent();
		while (!is_null($parent)) {
			if (is_a($parent, 'Pagemill_Tag_Loop')) {
				$hasLoop = true;
			}
			if ($parent->name() == $head) {
				break;
			}
			$parent = $parent->parent();
		}
		if (is_null($parent)) {
			throw new Exception("Recurse tag could not find '{$head}'");
		}
		if (!$hasLoop) {
			throw new Exception("Recurse tag requires a loop to process");
		}
		$parent = clone $parent;
		$parent->process($data, $stream);
	}
}
