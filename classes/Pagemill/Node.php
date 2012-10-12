<?php

abstract class Pagemill_Node {
	protected $parent = null;
	protected $doctype;
	/**
	 * Get the node's parent if it exists.
	 * @return Pagemill_Tag|null
	 */
	public function parent() {
		return $this->parent;
	}
	public function __construct(Pagemill_Doctype $doctype) {
		$this->doctype = $doctype;
	}
	abstract public function appendChild(Pagemill_Node $node);
	abstract public function appendText($text);
	abstract protected function output(Pagemill_Data $data, Pagemill_Stream $stream);
	abstract protected function rawOutput(Pagemill_Data $data, Pagemill_Stream $stream);
	abstract public function process(Pagemill_Data $data, Pagemill_Stream $stream);
}
