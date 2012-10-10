<?php

class Pagemill_Parser {
	private $_doctype;
	private $_xmlParser;
	private $_tagStack = array();
	private $_tagRegistry = array();
	private $_root = null;
	private $_currentCharacterData = '';
	public function __construct(Pagemill_Doctype $doctype = null) {
		if (is_null($doctype)) {
			$doctype = new Pagemill_Doctype('');
		}
		$this->_doctype = $doctype;
	}
	/**
	 * Parse a template string into a Tag tree.
	 * @param string $source The template code.
	 * @return Pagemill_Tag
	 */
	public function parse($source) {
		static $pagemillDoctype = null;
		if (is_null($pagemillDoctype)) {
			$pagemillDoctype = new Pagemill_Doctype_Template('pm');
		}
		$this->_root = null;
		$this->_tagRegistry = $pagemillDoctype->tagRegistry();
		// Check for an XML declaration
		$xmlDecl = '';
		$source = trim($source);
		if (preg_match('/^<\?xml ([\w\W\s\S]*?)\?>/', $source, $matches)) {
			$xmlDecl = $matches[0];
		}
		// Check for a doctype
		$doctype = '';
		if (preg_match('/^[\s\S]*?<\!DOCTYPE +([\w\W\s\S]*?)>/', substr($source, strlen($xmlDecl)), $matches)) {
			$parts = explode(' ', trim($matches[1]));
			$doctype = trim($parts[0]);
			$this->_doctype = Pagemill_Doctype::ForDoctype($doctype);
			$this->_tagRegistry = array_merge($this->_tagRegistry, $this->_doctype->tagRegistry());
			if (strpos($doctype, '[') === false) {
				$source = substr($source, 0, strlen($xmlDecl . $matches[0]) - 1) . "[\n" . $this->_doctype->entityReferences() . "\n]>" . substr($source, strlen($xmlDecl . $matches[0]));
			}
		}
		$this->_xmlParser = xml_parser_create();
		xml_parser_set_option($this->_xmlParser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($this->_xmlParser, array($this, '_xmlStartElement'), array($this, '_xmlEndElement'));
		xml_set_default_handler($this->_xmlParser, array($this, '_xmlDefault'));
		xml_set_character_data_handler($this->_xmlParser, array($this, '_xmlCharacter'));
		$result = xml_parse($this->_xmlParser, $source);
		if (!$result) {
			echo xml_error_string(xml_get_error_code($this->_xmlParser));
		}
		return $this->_root;
	}
	private function _xmlStartElement($parser, $name, $attributes) {
		// TODO: If current char data exists, it needs to be appended to the
		// last element
		$last = null;
		if (count($this->_tagStack)) {
			$last =& $this->_tagStack[count($this->_tagStack) - 1];
			if ($this->_currentCharacterData) {
				$last->appendText($this->_currentCharacterData);
				$this->_currentCharacterData = '';
			}
		}
		if (isset($this->_tagRegistry[$name])) {
			$cls = $this->_tagRegistry[$name];
			$tag = new $cls($name, $attributes, $last);
		} else {
			$tag = new Pagemill_Tag($name, $attributes, $last);
		}
		$this->_tagStack[] = $tag;
	}
	private function _xmlEndElement($parser, $name) {
		$last = array_pop($this->_tagStack);
		// TODO: If current char data exists, it needs to be appended to the
		// last element
		if (!count($this->_tagStack)) {
			$this->_root = $last;
		}
		$last->appendText($this->_currentCharacterData);
		$this->_currentCharacterData = '';
	}
	private function _xmlDefault($parser, $data) {
		$this->_currentCharacterData .= $data;
	}
	private function _xmlCharacter($parser, $data) {
		$this->_currentCharacterData .= $data;
	}
}
