<?php
class Pagemill_TagPreprocessor_ContentReplace extends Pagemill_TagPreprocessor {
	private $_expression;
	private $_replace;
	public function __construct($expression, $replace = false) {
		$this->_expression = $expression;
		$this->_replace = $replace;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$result = $data->evaluate($this->_expression);
		if ($result) {
			$tmp = new Pagemill_Tag($tag->name(), $tag->attributes(), $tag->parent());
			$tmp->appendText($result);
			if ($this->_replace) {
				$tmp->processInner($data, $stream);
			} else {
				$tmp->process($data, $stream);
			}
			$tmp->detach();
			return false;
		} else {
			if ($this->_replace) {
				$tag->processInner($data, $stream);
				return false;
			}
		}
		return true;
	}
}
