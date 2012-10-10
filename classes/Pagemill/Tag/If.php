<?php
class Pagemill_Tag_If extends Pagemill_Tag {
	private $_lastResult;
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$expr = $this->getAttribute('expr');
		if (strpos($expr, '@{') === false) {
			$expr = "@{{$expr}}@";
		}
		$result = $data->parseVariables($expr);
		if ($result) {
			foreach ($this->children() as $child) {
				$child->process($data, $stream);
			}
		}
		$this->_lastResult = $result;
	}
	public function lastResult() {
		return $this->_lastResult;
	}
}
