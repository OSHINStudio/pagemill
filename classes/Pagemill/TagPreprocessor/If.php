<?php
class Pagemill_TagPreprocessor_If extends Pagemill_TagPreprocessor {
	private $_expression;
	public function __construct($expression) {
		$this->_expression = $expression;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$result = $data->evaluate($this->_expression);
		if (!$result) {
			return false;
		}
	}
}
