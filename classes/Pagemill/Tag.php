<?php

class Pagemill_Tag extends Pagemill_Node {
	/**
	 * @var Pagemill_TagPreprocessor[]
	 */
	private $_before = array();
	protected $name;
	protected $attributes;
	private $_children = array();
	private $_doctype = null;
	protected $collapse = true;
	private $_header;
	private $_cachedName;
	private $_cachedAttributes;
	private $_cachedBefore;
	private $_processing = false;
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
		if (is_null($doctype)) {
			if (!is_null($parent)) {
				$this->_doctype = $parent->doctype();
			}
		} else {
			$this->_doctype = $doctype;
		}
	}
	public function attachPreprocess(Pagemill_TagPreprocessor $preprocess) {
		$this->_before[] = $preprocess;
	}
	public function hasPreprocessors() {
		return (count($this->_before) > 0);
	}
	public function name($withPrefix = true) {
		if ( (!$withPrefix) && ($index = strpos($this->name, ':')) !== false ) {
			return substr($this->name, $index + 1);
		}
		return $this->name;
	}
	public function attributes() {
		return $this->attributes;
	}
	public function children() {
		return $this->_children;
	}
	private function _before(Pagemill_Data $data, Pagemill_Stream $stream) {
		$final = true;
		while (count($this->_before) > 0) {
			$handler = array_shift($this->_before);
			if ($handler->process($this, $data, $stream) === false) {
				$final = false;
				break;
			}
		}
		return $final;
	}
	/**
	 * Process the tag and its children using the provided data and output the
	 * result to the provided stream. This method is final because it should
	 * be guaranteed to handle tag preprocessors and reset the tag to its
	 * original state. Tags that require special processing should override the
	 * output() method.
	 * @param Pagemill_Data $data
	 * @param Pagemill_Stream $stream
	 */
	final public function process(Pagemill_Data $data, Pagemill_Stream $stream) {
		// Changes made to the tag's name and attributes while processing
		// output are temporary.
		$this->_cachedName = $this->name;
		$this->_cachedAttributes = $this->attributes;
		$this->_cachedBefore = $this->_before;
		$this->_processing = true;
		$result = $this->_before($data, $stream);
		if ($result !== false) {
			if ($this->_header && !$this->parent) {
				$stream->puts(trim($this->_header) . "\n");
			}
			$this->output($data, $stream);
		}
		// Reset the tag's data for every iteration of process().
		$this->name = $this->_cachedName;
		$this->attributes = $this->_cachedAttributes;
		$this->_before = $this->_cachedBefore;
		$this->_processing = false;
	}
	/**
	 * Process the tag's children (but not the tag itself).
	 * @param Pagemill_Data $data
	 * @param Pagemill_Stream $stream
	 */
	final public function processInner(Pagemill_Data $data, Pagemill_Stream $stream) {
		foreach ($this->children() as $child) {
			$child->process($data, $stream);
		}
	}
	/**
	 * Build an attribute string from the tag's attributes. This method will
	 * evaluate expressions in attribute values.
	 * @param Pagemill_Data $data
	 * @return string
	 */
	protected function buildAttributeString(Pagemill_Data $data) {
		$string = '';
		foreach ($this->attributes as $key => $value) {
			$string .= ' ' . $key . '="' . $this->doctype()->encodeEntities($data->parseVariables($value)) . '"';
		}
		return $string;
	}
	/**
	 * Build a raw attribute string. This method will not evaluate expressions.
	 * @return string
	 */
	protected function buildRawAttributeString() {
		$string = '';
		foreach ($this->attributes as $key => $value) {
			$string .= ' ' . $key . '="' . $this->doctype()->encodeEntities($value) . '"';
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
		$stream->puts("<{$this->name()}");
		$stream->puts($this->buildAttributeString($data));
		if (count($this->children())) {
			$stream->puts(">");
			foreach ($this->children() as $child) {
				$child->process($data, $stream);
			}
			$stream->puts("</{$this->name()}>");
		} else {
			if ($this->collapse) {
				$stream->puts("/>");
			} else {
				$stream->puts("></{$this->name()}>");
			}
		}
	}
	/**
	 * Output the content of the tag without processing tags or evaluating
	 * expressions.
	 * @param Pagemill_Stream $stream
	 */
	protected function rawOutput(Pagemill_Stream $stream) {
		$stream->puts("<{$this->name()}");
		$stream->puts($this->buildRawAttributeString());
		if (count($this->children())) {
			$stream->puts(">");
			foreach ($this->children() as $child) {
				$child->rawOutput($stream);
			}
			$stream->puts("</{$this->name()}>");
		} else {
			if ($this->collapse) {
				$stream->puts("/>");
			} else {
				$stream->puts("></{$this->name()}>");
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
	final public function detach() {
		if ($this->parent) {
			$index = array_search($this, $this->parent->_children, true);
			if ($index !== false) {
				unset($this->parent->_children[$index]);
				$this->parent = null;
			} else {
				throw new Exception("Parent/child relationship is dodgy");
			}
		}
	}
	public function appendText($text) {
		if ($text !== '') {
			$node = new Pagemill_Node_Text($this->doctype());
			$node->appendText($text);
			$this->appendChild($node);
		}
	}
	public function replaceChild(Pagemill_Node $child, Pagemill_Node $replacement) {
		$index = array_search($child, $this->_children, true);
		if ($index === false) {
			throw new Exception('Child does not exist in node');
		}
		array_splice($this->_children, $index, 1, array($replacement));
		$replacement->_parent = $this;
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
	/**
	 * 
	 * @return Pagemill_Doctype
	 */
	public function doctype() {
		if (is_null($this->_doctype)) {
			if ($this->parent()) {
				return $this->parent()->doctype();
			}
		}
		return $this->_doctype;
	}
	public function header($text) {
		$this->_header = $text;
	}
	public function __clone() {
		parent::__clone();
		if ($this->_processing) {
			$this->name = $this->_cachedName;
			$this->attributes = $this->_cachedAttributes;
			$this->_before = $this->_cachedBefore;
			$this->_processing = false;
		}		
		$clonedChildren = array();
		foreach ($this->_children as $child) {
			$clonedChildren[] = clone $child;
		}
		$this->_children = array();
		foreach ($clonedChildren as $child) {
			$this->appendChild($child);
		}
	}
}
