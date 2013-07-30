<?php

class Pagemill_Parser {
	private $_doctype;
	//private $_xmlParser;
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
	private function createParser() {
		$parser = xml_parser_create('utf-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_element_handler($parser, array($this, '_xmlStartElement'), array($this, '_xmlEndElement'));
		xml_set_character_data_handler($parser, array($this, '_xmlCharacter'));
		return $parser;
	}
	/**
	 * Parse a template string into a Tag tree.
	 * @param string $source The template code.
	 * @return Pagemill_Tag
	 */
	public function parse($source, Pagemill_Doctype $doctype = null) {
		if (is_null($doctype)) {
			$doctype = new Pagemill_Doctype('', '');
		}
		$this->_doctype = $doctype;
		$this->_root = null;
		$this->_tagRegistry = array();
		$this->_namespaces = array();
		// Check for an XML declaration
		$xmlDecl = '';
		$source = trim($source);
		$ignoreLines = 0;
		$ignoreBytes = 0;
		if (preg_match('/^<\?xml ([\w\W\s\S]*?)\?>/', $source, $matches)) {
			$xmlDecl = $matches[0];
			$this->_xmlDeclString = $xmlDecl;
			$source = substr($source, strlen($xmlDecl));
		}
		$doctypeWithEntities = '';
		// Check for a doctype
		$doctypeFromSource = '';
		if (preg_match('/^[\s\S]*?<\!DOCTYPE +([\w\W\s\S]*?)>/', $source, $matches)) {
			$this->_doctypeString = trim($matches[0]);
			$parts = explode(' ', trim($matches[1]));
			$doctypeFromSource = trim($parts[0]);
			$this->_doctype = Pagemill_Doctype::ForDoctype($doctypeFromSource);
			//$this->_tagRegistry = array_merge($this->_tagRegistry, $this->_doctype->tagRegistry());
			//$this->_attributeRegistry = array_merge($this->_attributeRegistry, $this->_doctype->attributeRegistry());
			if (strpos($this->_doctypeString, '[') === false) {
				$ents = " [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>\n";
				$source = substr($source, 0, strlen($matches[0]) - 1) . $ents . substr($source, strlen($matches[0]));
				$ignoreLines = 3;
				$ignoreBytes = strlen($ents);
			}
		}
		if (!$doctypeFromSource && get_class($this->_doctype) == 'Pagemill_Doctype') {
			// No doctype detected. Try the root element
			if (preg_match('/<([a-z0-9\-_]+)/i', $source, $matches)) {
				$doctype = $matches[1];
				$this->_doctype = Pagemill_Doctype::ForDoctype($matches[1]);
				//$this->_tagRegistry = array_merge($this->_tagRegistry, $this->_doctype->tagRegistry());
				//$this->_attributeRegistry = array_merge($this->_attributeRegistry, $this->_doctype->attributeRegistry());
				if ($this->_doctype->entities()) {
					$doctypeWithEntities = "<!DOCTYPE {$matches[1]} [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>\n";
					$ignoreLines = 3;
				}
				//if ($this->_doctype->entities()) {
				//	$source = substr($source, 0, strlen($xmlDecl)) . "\n<!DOCTYPE {$matches[1]} [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>" . substr($source, strlen($xmlDecl));
				//}
			}
		} else if (!$doctypeFromSource) {
			if ($this->_doctype->entities()) {
				$doctypeWithEntities = "<!DOCTYPE root [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>\n";
				$ignoreLines = 3;
				//$source = substr($source, 0, strlen($xmlDecl)) . "\n<!DOCTYPE _root_ [\n" . $this->_entityReferences($this->_doctype->entities()) . "\n]>" . substr($source, strlen($xmlDecl));
			}
		}
		$source = str_replace('<!--@', '<_tmplcomment><![CDATA[', $source);
		$source = str_replace('@-->', ']]></_tmplcomment>', $source);
		$source = str_replace('<!--', '<_comment><![CDATA[', $source);
		$source = str_replace('-->', ']]></_comment>', $source);
		$parser = $this->createParser();
		$result = xml_parse($parser, $doctypeWithEntities . $source, true);
		if (!$result) {
			$ec = xml_get_error_code($parser);
			// PHP 5.3.10 returns less granular error codes with syntax errors
			// being more common, so always assume a second attempt should be
			// performed for <= 5.3.10.
			if (version_compare(phpversion(), '5.3.10') < 1 || (($ec == 4 || $ec == 5 || $ec == 9) && !$this->_xmlDeclString && !$this->_doctypeString)) {
				$this->_namespaces = array();
				xml_parser_free($parser);
				$parser = $this->createParser();
				$ignoreBytes = strlen('<pm:template xmlns:pm="http://typeframe.com/pagemill">');
				$result = xml_parse($parser, $doctypeWithEntities . '<pm:template xmlns:pm="http://typeframe.com/pagemill">' . $source . '</pm:template>', true);
			}
		}
		if (!$result) {
			$line = xml_get_current_line_number($parser) - $ignoreLines;
			$column = xml_get_current_column_number($parser);
			$index = xml_get_current_byte_index($parser) - strlen($doctypeWithEntities) - $ignoreBytes;
			$this->_throwException(xml_get_error_code($parser), $source, $line, $column, $index);
		}
		xml_parser_free($parser);
		return $this->_root;
	}
	private function _throwException($errorCode, $source, $line, $column, $index) {
		switch ($errorCode) {
			case 26:
				$left = strrpos($source, '&', $index - strlen($source));
				if ($left !== false) {
					$entity = substr($source, $left + 1, $index - $left - 2);
					throw new Exception("Undeclared entity '{$entity}' on line {$line}, column {$column}");
				}
				break;
			case 68:
				if (substr($source, $index - 2, 1) == '&') {
					throw new Exception("Ampersand without entity name on line {$line}, column {$column}");
				}
				break;
		}
		throw new Exception('Error #' . $errorCode . ': ' . xml_error_string($errorCode) . " on line {$line}, column {$column}");
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
		foreach ($attributes as $k => $v) {
			if ($k == 'xmlns' || substr($k, 0, 6) == 'xmlns:') {
				$doctype = Pagemill_Doctype::ForNamespaceUri($v, substr($k, 6));
				$currentDoctype->merge($doctype);
				if (!$doctype->keepNamespaceDeclarationInOutput()) {
					unset($attributes[$k]);
				}
			} else if (substr($k, 0, 3) == 'pm:' && !isset($this->_namespaces['pm'])) {
				// Declare the Template doctype using the default pm prefix
				$this->_namespaces['pm'] = 'http://typeframe.com/pagemill';
				$pm = Pagemill_Doctype::GetTemplateDoctype('pm');
				$currentDoctype->merge($pm);
			}
		}
		if (substr($name, 0, 3) == 'pm:' && !isset($this->_namespaces['pm'])) {
			// Declare the Template doctype using the default pm prefix
			$this->_namespaces['pm'] = 'http://typeframe.com/pagemill';
			$pm = Pagemill_Doctype::GetTemplateDoctype('pm');
			$currentDoctype->merge($pm);
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
	private function _nodeIsCombinable(Pagemill_Node $node) {
		static $combinableClasses = array(
			'Pagemill_Tag',
			'Pagemill_Tag_AlwaysExpand',
			'Pagemill_Tag_NoOutput'
		);
		return ( 
				(in_array(get_class($node), $combinableClasses) && !$node->hasPreprocessors() && strpos($node->name(), ':') === false)
				|| $node instanceof Pagemill_Node_Text
				|| $node instanceof Pagemill_Node_Frag
		);
	}
	private function _xmlEndElement($parser, $name) {
		/* @var Pagemill_Tag */
		$last = array_pop($this->_tagStack);
		$last->appendText($this->_currentCharacterData);
		$this->_currentCharacterData = '';
		$attributeRegistry = $last->doctype()->attributeRegistry();
		foreach ($last->attributes() as $k => $v) {
			if (isset($attributeRegistry[$k])) {
				$cls = $attributeRegistry[$k];
				$attribute = new $cls($k, $v, $last);
			}
		}
		// Check if children are combinable
		$combinable = false;
		if ($last->parent()) {
			if ($this->_nodeIsCombinable($last)) {
				$combinable = true;
				foreach ($last->children() as $child) {
					if (count($child->children())) {
						$combinable = false;
						break;
					}
					if (!$this->_nodeIsCombinable($child)) {
						$combinable = false;
						break;
					}
				}
			}
		}
		if ($combinable) {
			$frag = new Pagemill_Node_Frag($last->doctype());
			//foreach ($last->children() as $child) {
			//	$frag->appendChild($last);
			//}
			$frag->appendChild($last);
			$last->parent()->appendChild($frag);
			$last->detach();
			$last = $frag;
		}
		if (!count($this->_tagStack)) {
			$this->_root = $last;
		}
	}
	private function _xmlCharacter($parser, $data) {
		$this->_currentCharacterData .= $data;
	}
}
