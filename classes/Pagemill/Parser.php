<?php

class Pagemill_Parser {
	private $_doctype;
	private $_xmlParser;
	private $_tagStack = array();
	//private $_tagRegistry = array();
	private $_xmlDeclString = '';
	private $_doctypeString = '';
	//private $_attributeRegistry = array();
	private $_root = null;
	private $_currentCharacterData = '';
	private $_namespaces;
	public function __construct() {
		
	}
	private function _entityReferences($entities) {
		$code = '';
		foreach ($entities as $k => $v) {
			// Solution found at http://us3.php.net/ord (darien at etelos dot com 19-Jan-2007 12:27).
			$kbe = mb_convert_encoding($k, 'UCS-4BE', 'UTF-8');
			for ($i = 0; $i < mb_strlen($kbe, 'UCS-4BE'); ++$i) {
				$kbe2      = mb_substr($kbe, $i, 1, 'UCS-4BE');
				$ord       = unpack('N', $kbe2);
				$code .= sprintf('<!ENTITY %s "&#%s;">', substr($v, 1, -1), $ord[1]);
			}
		}
		return $code;
	}
	/**
	 * Parse a template string into a Tag tree.
	 * @param string $source The template code.
	 * @return Pagemill_Tag
	 */
	public function parse($source, Pagemill_Doctype $doctype = null) {
		if (is_null($doctype)) {
			$doctype = new Pagemill_Doctype('');
		}
		$this->_doctype = $doctype;
		$this->_root = null;
		$this->_tagRegistry = array();
		$this->_namespaces = array();
		// Check for an XML declaration
		$xmlDecl = '';
		$source = trim($source);
		if (preg_match('/^<\?xml ([\w\W\s\S]*?)\?>/', $source, $matches)) {
			$xmlDecl = $matches[0];
			$this->_xmlDeclString = $xmlDecl;
		}
		// Check for a doctype
		$doctypeFromSource = '';
		if (preg_match('/^[\s\S]*?<\!DOCTYPE +([\w\W\s\S]*?)>/', substr($source, strlen($xmlDecl)), $matches)) {
			$this->_doctypeString = trim($matches[0]);
			$parts = explode(' ', trim($matches[1]));
			$doctypeFromSource = trim($parts[0]);
			$this->_doctype = Pagemill_Doctype::ForDoctype($doctypeFromSource);
			//$this->_tagRegistry = array_merge($this->_tagRegistry, $this->_doctype->tagRegistry());
			//$this->_attributeRegistry = array_merge($this->_attributeRegistry, $this->_doctype->attributeRegistry());
			if (strpos($this->_doctypeString, '[') === false) {
				$source = substr($source, 0, strlen($xmlDecl . $matches[0]) - 1) . "[\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>" . substr($source, strlen($xmlDecl . $matches[0]));
			}
		}
		if (!$doctypeFromSource) {
			// No doctype detected. Try the root element
			if (preg_match('/<([a-z0-9\-_]+)/i', $source, $matches)) {
				$doctype = $matches[1];
				$this->_doctype = Pagemill_Doctype::ForDoctype($matches[1]);
				//$this->_tagRegistry = array_merge($this->_tagRegistry, $this->_doctype->tagRegistry());
				//$this->_attributeRegistry = array_merge($this->_attributeRegistry, $this->_doctype->attributeRegistry());
				$source = substr($source, 0, strlen($xmlDecl)) . "\n<!DOCTYPE {$matches[1]} [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>" . substr($source, strlen($xmlDecl));
			}
		}
		$this->_xmlParser = xml_parser_create('utf-8');
		xml_parser_set_option($this->_xmlParser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($this->_xmlParser, array($this, '_xmlStartElement'), array($this, '_xmlEndElement'));
		//xml_set_default_handler($this->_xmlParser, array($this, '_xmlDefault'));
		xml_set_character_data_handler($this->_xmlParser, array($this, '_xmlCharacter'));
		$result = xml_parse($this->_xmlParser, $source);
		if (!$result) {
			throw new Exception(xml_error_string(xml_get_error_code($this->_xmlParser)));
		}
		return $this->_root;
	}
	private function _declareNamespace($prefix, $uri) {
		if (isset($this->_namespaces[$prefix])) {
			throw new Exception("Namespace prefix {$prefix} declared more than once");
		}
		$this->_namespaces[$prefix] = $uri;
		$doctype = Pagemill_Doctype::ForNamespaceUri($uri, $prefix);
		return $doctype;
	}
	private function _xmlStartElement($parser, $name, $attributes) {
		$last = null;
		if (count($this->_tagStack)) {
			$last =& $this->_tagStack[count($this->_tagStack) - 1];
			if ($this->_currentCharacterData) {
				$last->appendText($this->_currentCharacterData);
				$this->_currentCharacterData = '';
			}
		}
		if ($last) {
			$currentDoctype = $last->doctype();
		} else {
			$currentDoctype = $this->_doctype;
		}
		if (substr($name, 0, 3) == 'pm:' && !isset($this->_namespaces['pm'])) {
			// Declare the Template doctype using the default pm prefix
			$this->_namespaces['pm'] = 'http://typeframe.com/pagemill';
			$pm = new Pagemill_Doctype_Template('pm');
			$currentDoctype->merge($pm);
		}
		foreach ($attributes as $k => $v) {
			if ($k == 'xmlns' || substr($k, 0, 6) == 'xmlns:') {
				$result = $this->_declareNamespace(substr($k, 6), $v);
				$currentDoctype->merge($result);
				if (!$result->keepNamespaceDeclarationInOutput()) {
					unset($attributes[$k]);
				}
			} else if (substr($k, 0, 3) == 'pm:' && !isset($this->_namespaces['pm'])) {
				// Declare the Template doctype using the default pm prefix
				$this->_namespaces['pm'] = 'http://typeframe.com/pagemill';
				$pm = new Pagemill_Doctype_Template('pm');
				$currentDoctype->merge($pm);
			}
		}
		$tagRegistry = $currentDoctype->tagRegistry();
		if (isset($tagRegistry[$name])) {
			$cls = $tagRegistry[$name];
			$tag = new $cls($name, $attributes, $last, $currentDoctype);
		} else {
			$tag = new Pagemill_Tag($name, $attributes, $last, $currentDoctype);
		}
		if (!count($this->_tagStack)) {
			// This appears to be a root element, so append the headers.
			$header = trim("{$this->_xmlDeclString}\n{$this->_doctypeString}\n");
			$tag->header($header);
		}
		$this->_tagStack[] = $tag;
	}
	private function _xmlEndElement($parser, $name) {
		$last = array_pop($this->_tagStack);
		$last->appendText($this->_currentCharacterData);
		$this->_currentCharacterData = '';
		$attributeRegistry = $last->doctype()->attributeRegistry();
		foreach (array_reverse($last->attributes()) as $k => $v) {
			if (isset($attributeRegistry[$k])) {
				$cls = $attributeRegistry[$k];
				// TODO: What about the doctype and the template? Probably
				// don't need them if they're in the tag, right?
				$attribute = new $cls($k, $v, $last);
				$last = $attribute->tag();
				if (count($this->_tagStack)) {
					$this->_tagStack[count($this->_tagStack) - 1]->appendChild($last);
				}
			}
		}
		if (!count($this->_tagStack)) {
			$this->_root = $last;
		}
	}
	private function _xmlDefault($parser, $data) {
		$this->_currentCharacterData .= $data;
	}
	private function _xmlCharacter($parser, $data) {
		$this->_currentCharacterData .= $data;
	}
}
