<?php

class Pagemill_Tag extends Pagemill_Node {
	private $_before = array();
	private $_after = array();
	private $_originalName;
	private $_originalAttributes;
	protected $name;
	protected $attributes;
	private $_children = array();
	/**
	 * Events that occur BEFORE the tag is processed receive an object with
	 * two properties: a Pagemill_Tag and a Pagemill_Data.
	 */
	const EVENT_BEFORE = 'before';
	/**
	 * Events that occur AFTER the tag is processed receive a
	 * Pagemill_SimpleXmlElement.
	 */
	const EVENT_AFTER = 'after';
	/**
	 * Initialize the tag.
	 * @param string $name The name of the tag (i.e., the XML element name).
	 * @param array $attributes The tag/element attributes.
	 */
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null) {
		//$this->attach(self::EVENT_BEFORE, new Pagemill_Tag_Event_AttributeHandler());
		$this->_originalName = $name;
		$this->_originalAttributes = $attributes;
		$this->name = $name;
		$this->attributes = $attributes;
		if ($parent) {
			$parent->appendChild($this);
		}
	}
	protected function attachPreprocess(Pagemill_Tag_Preprocess $preprocess) {
		$this->_before[] = $preprocess;
	}
	public function name() {
		return $this->name;
	}
	public function attributes() {
		return $this->attributes;
	}
	public function children() {
		return $this->_children;
	}
	private function _before(Pagemill_Data $data) {
		// Reset the tag's data for every iteration of process().
		$this->name = $this->_originalName;
		$this->attributes = $this->_originalAttributes;
		foreach ($this->_before as $handler) {
			$handler->process($this, $data);
		}
	}
	final public function process(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->_before($data);
		$this->output($data, $stream);
	}
	protected function buildAttributeString(Pagemill_Data $data) {
		$string = '';
		foreach ($this->attributes as $key => $value) {
			$string .= ' ' . $key . '="' . htmlentities($value) . '"';
		}
		return $string;
	}
	/**
	 * Output the processed tag to a stream.
	 * @param Pagemill_Data $data The current data node.
	 * @param Pagemill_Stream $stream The stream that accepts output.
	 * @return string
	 */
	protected function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->append("<{$this->name()}");
		$stream->append($this->buildAttributeString($data));
		$stream->append(">");
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
		$stream->append("</{$this->name()}>");
	}
	/**
	 * Append a child node to the element.
	 * @param Pagemill_Node $node
	 */
	final public function appendChild(Pagemill_Node $node) {
		if ($node->parent) {
			throw new Exception("Appended child already has a parent");
		}
		$this->_children[] = $node;
		$node->parent = $this;
	}
	public function appendText($text) {
		$node = new Pagemill_Node_Text();
		$node->appendText($text);
		$this->appendChild($node);
	}
	public function getAttribute($name) {
		return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
	}
	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
	}
}
