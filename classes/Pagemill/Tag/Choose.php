<?php

class Pagemill_Tag_Choose extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			if (is_a($child, 'Pagemill_tag')) {
				if ($child->name == 'pm:when') {
					$expr = $child->getAttribute('expr');
					if (strpos($expr, '@{') === false) {
						$expr = "@{" . $expr . "}@";
					}
					$value = $data->parseVariables($expr);
					if ($value) {
						$child->process($data, $stream);
						return;
					}
				} else if ($child->name == 'pm:otherwise') {
					$child->process($data, $stream);
					return;
				}
			}
		}
	}
}
