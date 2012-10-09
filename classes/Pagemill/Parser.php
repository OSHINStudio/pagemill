<?php

class Pagemill_Parser {
	private $_doctype;
	private $_xmlParser;
	public function __construct(Pagemill_Doctype $doctype = null) {
		if (is_null($doctype)) {
			$doctype = new Pagemill_Doctype();
		}
		$this->_doctype = $doctype;
	}
	public function parse($source) {
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
	}
	private function _xmlStartElement($parser, $name, $attributes) {
		echo "Parsing a {$name}<br/>";
	}
	private function _xmlEndElement($parser, $name) {
		echo "Ending {$name}<br/>";
	}
	private function _xmlDefault($parser, $data) {
		echo "Handling {$data}<br/>";
		return true;
	}
	private function _xmlCharacter($parser, $data) {
		echo "Hey, I'm ready for {$data}<br/>";
	}
}
