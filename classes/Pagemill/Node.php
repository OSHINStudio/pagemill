<?php

abstract class Pagemill_Node {
	protected $parent = null;
	/**
	 * Get the node's parent if it exists.
	 * @return Pagemill_Tag|null
	 */
	public function parent() {
		return $this->parent;
	}
	abstract public function appendChild(Pagemill_Node $node);
	abstract public function appendText($text);
	abstract protected function output(Pagemill_Data $data, Pagemill_Stream $stream);
	abstract public function process(Pagemill_Data $data, Pagemill_Stream $stream);
}
