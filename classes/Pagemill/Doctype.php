<?php

class Pagemill_Doctype implements Pagemill_DoctypeInterface {
	private static $_doctypes = array();
	private static $_extensions = array();
	private static $_namespaceUris = array();
	private $_tagRegistry = array();
	private $_attributeRegistry = array();
	private $_nsPrefix = '';
	public function __construct($nsPrefix) {
		$this->_nsPrefix = $nsPrefix;
	}
	public function nsPrefix() {
		return $this->_nsPrefix;
	}
	public function entityReferences() {
		return '';
	}
	public function encodeEntities($text) {
		return $text;
	}
	public function decodeEntities($text) {
		return $text;
	}
	protected function registerTag($tag, $class) {
		$this->_tagRegistry[($this->_nsPrefix ? "{$this->_nsPrefix}:" : '') . $tag] = $class;
	}
	protected function registerAttribute($attribute, $class) {
		$this->_attributeRegistry[($this->_nsPrefix ? "{$this->_nsPrefix}:" : '') . $attribute] = $class;
	}
	public function tagRegistry() {
		return $this->_tagRegistry;
	}
	public function attributeRegistry() {
		return $this->_attributeRegistry;
	}
	public static function ForFile($filename, $prefix = '') {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$cls = (isset(self::$_extensions[$extension]) ? self::$_extensions[$extension] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	public static function ForDoctype($doctype, $prefix = '') {
		$cls = (isset(self::$_doctypes[$doctype]) ? self::$_doctypes[$doctype] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	public static function ForNamespaceUri($uri, $prefix = '') {
		$cls = (isset(self::$_namespaceUris[$uri]) ? self::$_namespaceUris[$uri] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls($prefix);
	}
	public static function RegisterDoctype($root, $class) {
		self::$_doctypes[$root] = $class;
	}
	public static function RegisterFileExtension($extension, $class) {
		self::$_extensions[strtolower($extension)] = $class;
	}
	public static function RegisterNamespaceUri($uri, $class) {
		self::$_namespaceUris[$uri] = $class;
	}
}

Pagemill_Doctype::RegisterDoctype('html', 'Pagemill_Doctype_Html', 'http://www.w3.org/html5/whatever');
Pagemill_Doctype::RegisterFileExtension('html', 'Pagemill_Doctype_Html', 'http://www.w3.org/html5/whatever');
