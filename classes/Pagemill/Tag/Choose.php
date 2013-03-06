<?php

class Pagemill_Tag_Choose extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		/* @var $child Pagemill_Tag */
		foreach ($this->children() as $child) {
			if (is_a($child, 'Pagemill_tag')) {
				if ($child->name(false) == 'when') {
					$expr = $child->getAttribute('expr');
					if (strpos($expr, '@{') === false) {
						$expr = "@{" . $expr . "}@";
					}
					$value = $data->parseVariables($expr);
					if ($value) {
						$child->processInner($data, $stream);
						return;
					}
				} else if ($child->name(false) == 'otherwise') {
					$child->processInner($data, $stream);
					return;
				}
			}
		}
	}
}
