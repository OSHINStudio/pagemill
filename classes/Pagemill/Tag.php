<?php

class Pagemill_Tag extends Pagemill_Node {
	private $_before = array();
	protected $name;
	protected $attributes;
	private $_children = array();
	private $_xmlDecl = '';
	private $_doctype = '';
	protected $collapse = true;
	private $_processing = false;
	private $_header;
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
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		//$this->attach(self::EVENT_BEFORE, new Pagemill_Tag_Event_AttributeHandler());
		$this->_originalName = $name;
		$this->_originalAttributes = $attributes;
		$this->name = $name;
		$this->attributes = $attributes;
		if ($parent) {
			$parent->appendChild($this);
		}
		$this->_doctype = $doctype;
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
		//$this->name = $this->_originalName;
		//$this->attributes = $this->_originalAttributes;
		foreach ($this->_before as $handler) {
			$handler->process($this, $data);
		}
	}
	final public function process(Pagemill_Data $data, Pagemill_Stream $stream) {
		// Changes made to the tag's name and attributes while processing
		// output are temporary.
		$cachedName = $this->name;
		$cachedAttributes = $this->attributes;
		if (is_null($this->parent())) {
			/*if ($this->_xmlDecl) {
				$stream->append("{$this->_xmlDecl}\n");
			}
			if ($this->_doctype) {
				$stream->append("{$this->_doctype}\n");
			}*/
		}
		$this->_before($data);
		if ($this->_header && !$this->parent) {
			$stream->append(trim($this->_header) . "\n");
		}
		$this->output($data, $stream);
		$this->name = $cachedName;
		$this->attributes = $cachedAttributes;
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
		if (count($this->children())) {
			$stream->append(">");
			foreach ($this->children() as $child) {
				$child->process($data, $stream);
			}
			$stream->append("</{$this->name()}>");
		} else {
			if ($this->collapse) {
				$stream->append("/>");
			} else {
				$stream->append("></{$this->name()}>");
			}
		}
	}
	/**
	 * Append a child node to the element.
	 * @param Pagemill_Node $node
	 */
	final public function appendChild(Pagemill_Node $node) {
		if ($node->parent) {
			if(($key = array_search($node, $node->parent->_children, true)) !== false) {
				unset($node->parent->_children[$key]);
			} else {
				throw new Exception("Unable to detach child from current parent");
			}
		}
		$this->_children[] = $node;
		$node->parent = $this;
	}
	public function appendText($text) {
		if ($text !== '') {
			$node = new Pagemill_Node_Text();
			$node->appendText($text);
			$this->appendChild($node);
		}
	}
	public function getAttribute($name) {
		return (isset($this->attributes[$name]) ? $this->attributes[$name] : null);
	}
	public function setAttribute($name, $value) {
		$this->attributes[$name] = $value;
	}
	public function hasAttribute($name) {
		return isset($this->attributes[$name]);
	}
	public function removeAttribute($name) {
		unset($this->attributes[$name]);
	}
	public function doctype() {
		return $this->_doctype;
	}
	public function header($text) {
		$this->_header = $text;
	}
}
