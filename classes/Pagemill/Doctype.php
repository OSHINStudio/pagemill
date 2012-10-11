<?php

class Pagemill_Doctype implements Pagemill_DoctypeInterface {
	private static $_doctypes = array();
	private static $_extensions = array();
	private static $_namespaceUris = array();
	private $_tagRegistry = array();
	private $_attributeRegistry = array();
	private $_entities = array();
	protected $keepNamespaceDeclarationInOutput = true;
	private $_nsPrefix = '';
	public function __construct($nsPrefix) {
		$this->_nsPrefix = $nsPrefix;
	}
	public function nsPrefix() {
		return $this->_nsPrefix;
	}
	public function entities() {
		return $this->_entities;
	}
	public function addEntity($text, $entity) {
		$this->_externalEntities[$text] = $entity;
	}
	public function addEntityArray($array) {
		$this->_entities = array_merge($this->_entities, $array);
	}
	public function encodeEntities($text) {
		return strtr($text, $this->_entities);
	}
	/**
	 * Register a tag for the Pagemill to process.
	 * @param string $tag The tag (element) name. If the name begins with a
	 * slash (e.g., "/mytag"), it will be registered without a namespace prefix.
	 * @param string $class The name of the Pagemill_Tag class that will process
	 * the tag.
	 */
	protected function registerTag($tag, $class) {
		if (substr($tag, 0, 1) == '/') {
			$tag = substr($tag, 1);
		} else {
			$tag = ($this->_nsPrefix ? "{$this->_nsPrefix}:" : '') . $tag;
		}
		$this->_tagRegistry[$tag] = $class;
	}
	/**
	 * Register an attribute for the Pagemill to process.
	 * @param string $attribute The attribute name. If the name begins with a
	 * slash (e.g., "/name"), it will be registered without a namespace prefix.
	 * @param string $class The name of the Pagemill_Attribute class that will
	 * process the attribute.
	 */
	protected function registerAttribute($attribute, $class) {
		if (substr($attribute, 0, 1) == '/') {
			$attribute = substr($attribute, 1);
		} else {
			$attribute = ($this->_nsPrefix ? "{$this->_nsPrefix}:" : '') . $attribute;
		}
		$this->_attributeRegistry[$attribute] = $class;
	}
	/**
	 * Get an array of registered tags.
	 * @return array
	 */
	public function tagRegistry() {
		return $this->_tagRegistry;
	}
	/**
	 * Get an array of registered attributes.
	 * @return array
	 */
	public function attributeRegistry() {
		return $this->_attributeRegistry;
	}
	/**
	 * Merge another doctype into this one.
	 * @param Pagemill_Doctype $next
	 */
	public function merge(Pagemill_Doctype $next) {
		$this->_tagRegistry = array_merge($this->_tagRegistry, $next->_tagRegistry);
		$this->_attributeRegistry = array_merge($this->_attributeRegistry, $next->_attributeRegistry);
		$this->_entities = array_merge($this->_entities, $next->_entities);
	}
	/**
	 * Find a Doctype for a file by its extension.
	 * @param string $filename The name of the file.
	 * @param string $prefix
	 * @return Pagemill_Doctype
	 */
	public static function ForFile($filename, $prefix = '') {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$cls = (isset(self::$_extensions[$extension]) ? self::$_extensions[$extension] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	/**
	 * Find a Doctype for a file by its <!DOCTYPE> declaration or the name
	 * of its root element.
	 * @param string $doctype The doctype declaration or root element name.
	 * @param string $prefix
	 * @return Pagemill_Doctype
	 */
	public static function ForDoctype($doctype, $prefix = '') {
		$cls = (isset(self::$_doctypes[$doctype]) ? self::$_doctypes[$doctype] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	/**
	 * Find a Doctype for a namespace URI.
	 * @param string $uri The namespace URI.
	 * @param string $prefix
	 * @return Pagemill_Doctype
	 */
	public static function ForNamespaceUri($uri, $prefix = '') {
		$cls = (isset(self::$_namespaceUris[$uri]) ? self::$_namespaceUris[$uri] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	/**
	 * Register a Doctype for a <!DOCTYPE> declaration or root element name.
	 * @param string $root The root element name.
	 * @param string $class The name of the Pagemill_Doctype class.
	 */
	public static function RegisterDoctype($root, $class) {
		self::$_doctypes[$root] = $class;
	}
	/**
	 * Register a Doctype for a file extension.
	 * @param string $extension The file extension.
	 * @param string $class The name of the Pagemill_Doctype class.
	 */
	public static function RegisterFileExtension($extension, $class) {
		self::$_extensions[strtolower($extension)] = $class;
	}
	/**
	 * Register a Doctype for a namespace URI.
	 * @param string $uri The namespace URI.
	 * @param string $class The name of the Pagemill_Doctype class.
	 */
	public static function RegisterNamespaceUri($uri, $class) {
		self::$_namespaceUris[$uri] = $class;
	}
	/**
	 * If the doctype was called from a namespace declaration, the Parser uses
	 * this value to determine whether to keep the declaration in the generated
	 * output. True by default.
	 * @return boolean
	 */
	public function keepNamespaceDeclarationInOutput() {
		return $this->keepNamespaceDeclarationInOutput;
	}
}

Pagemill_Doctype::RegisterDoctype('html', 'Pagemill_Doctype_Html', 'http://www.w3.org/html5/whatever');
Pagemill_Doctype::RegisterFileExtension('htm', 'Pagemill_Doctype_Html', 'http://www.w3.org/html5/whatever');
Pagemill_Doctype::RegisterFileExtension('html', 'Pagemill_Doctype_Html', 'http://www.w3.org/html5/whatever');
Pagemill_Doctype::RegisterNamespaceUri('http://typeframe.com/pagemill', 'Pagemill_Doctype_Template');
