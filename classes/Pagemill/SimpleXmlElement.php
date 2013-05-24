<?php
/**
 * An extension of the SimpleXMLElement class. Features include CSS selector
 * queries, Pagemill namespace injection, UTF-8 conversion, and correct handling
 * of XML entities (the base SimpleXmlElement class mangles entities in child
 * elements).
 */
class Pagemill_SimpleXmlElement extends SimpleXMLElement {
	/**
	 * Get or set the element's default namespace.
	 * @param string $ns The default namespace to set
	 * @return string|null The default namespace or null if none was set
	 */
	private function _defaultNamespace($ns = null) {
		static $defaultNamespace = null;
		if (!is_null($ns)) {
			$defaultNamespace = $ns;
		}
		return $defaultNamespace;
	}
	/**
	 * Return a string containing the element's inner XML. Similar to the
	 * innerHTML function in browser DOM interfaces.
	 * @return string The XML fragment.
	 */
	public function innerXml() {
		$name = $this->getName();
		// Check for a root namespace
		$ns = $this->getNamespaces(false);
		$keys = array_keys($ns);
		if ($keys) {
			$name = "{$keys[0]}:{$name}";
		}
		$xml = $this->asXml();
		$startTag = mb_strpos($xml, '<' . $name);
		$startInner = mb_strpos($xml, '>', $startTag) + 1;
		$endInner = mb_strrpos($xml, '<');
		$length = $endInner - $startInner;
		$inner = mb_substr($xml, $startInner, $length);
		$inner = self::_ConvertUtf8ToXmlEntities($inner);
		return $inner;
	}
	/**
	 * Query the document using a CSS selector.
	 * @param string $selector The CSS selector.
	 * @return array The matching elements.
	 */
	public function select($selector) {
		$query = Zend_Dom_Query_Css2Xpath::transform($selector);
		return $this->xpath($query);
	}
	/**
	 * Query the document using XPath. This extension of the base xpath()
	 * function automatically registers the Pagemill namespace if the document
	 * uses it without declaring it.
	 * @param string $path The XPath query.
	 * @return array The matching elements.
	 */
	public function xpath($path) {
		if ( (strpos($path, 'pm:') !== false) && (!in_array('pm', array_keys($this->getNamespaces()))) ) {
			$this->registerXPathNamespace('pm', 'http://typeframe.com/pagemill');
		}
		return parent::xpath($path);
	}
	/**
	 * Create a Pagemill_SimpleXmlElement from a file. This function supports
	 * HTML entities, Pagemill namespace injection, UTF-8 conversion, and
	 * several other corrective measures.
	 * @param string $filename The XML file.
	 * @return Pagemill_SimpleXmlElement
	 */
	public static function LoadFile($filename) {
		$cls = get_called_class();
		$string = file_get_contents($filename);
		$xml = $cls::LoadString($string);
		if (is_null($xml)) {
			trigger_error("Failed to load {$filename}");
		}
		return $xml;
	}
	/**
	 * Create a Pagemill_SimpleXmlElement from a string. This function supports
	 * HTML entities, Pagemill namespace injection, UTF-8 conversion, and
	 * several other corrective measures.
	 * @param string $string The XML string.
	 * @return Pagemill_SimpleXmlElement
	 */
	public static function LoadString($string) {
		if (!mb_check_encoding($string, 'utf-8')) {
			$string = mb_convert_encoding($string, 'utf-8');
		}
		// TODO: A nasty hack to fix the fact that SimpleXML mistreats default namespaces in XPath queries
		$defaultNamespace = '';
		if (preg_match('/xmlns="[\w\W\s\S]*?"/', $string, $matches)) {
			$defaultNamespace = $matches[0];
			$string = preg_replace('/xmlns="[\w\W\s\S]*?"/', '', $string);
		}
		$cls = get_called_class();
		$string = self::_ConvertUtf8ToXmlEntities($string);
		$previous = libxml_use_internal_errors(true);
		libxml_clear_errors();
		try {
			$xml = new $cls($string, 0, false);
		} catch(Exception $e) {
			$xml = null;
		}
		$errors = libxml_get_errors();
		if ($errors) {
			$string = self::_FixErrors($string, $errors);
			libxml_clear_errors();
			try {
				$xml = new $cls($string, 0, false);
			} catch(Exception $e) {
				$errors = libxml_get_errors();
				if ($errors) {
					foreach ($errors as $error) {
						trigger_error("{$error->message} (level {$error->level}, code {$error->code}, line {$error->line}");
					}
				} else {
					trigger_error("Document could not be parsed (unknown error)");
				}
				$xml = null;
			}
		}
		//$xml->_registerNamespaces();
		libxml_use_internal_errors($previous);
		if ($xml) {
			$xml->_defaultNamespace($defaultNamespace);
		}
		return $xml;
	}
	protected static function _InsertNamespace($namespace, $string) {
		$skip = '';
		preg_match('/<\?xml[\w\W\s\S]*?\?>/', $string, $matches);
		if ($matches) {
			$skip .= '<\?xml[\w\W\s\S]*?\?>';
		}
		preg_match('/<\?[\w\W\s\S]*?\?>/', $string, $matches);
		if ($matches) $header = $matches[0];
		$doctype = strpos(trim($string), '<!DOCTYPE ');
		if ($doctype === 0) {
			$skip .= '<[^>]*?>';
		}
		$string = preg_replace('/(' . $skip . '[^>]*)>/', '$1 ' . $namespace . '>', $string, 1);
		return $string;
	}
	protected static function _FixErrors($string, $errors) {
		$fixed201 = false;
		foreach ($errors as $error) {
			if ( ($error->code == 201) && (!$fixed201) ) {
				if (strpos($string, 'xmlns:pm') === false) {
					if ( (strpos($string, '<pm:') !== false) || (preg_match('/<[^>]*?pm\:[\w\W]*?\=/', $string)) ) {
						// This XML document appears to use the Pagemill namespace without declaring it.
						$string = self::_InsertNamespace('xmlns:pm="http://typeframe.com/pagemill"', $string);
						libxml_clear_errors();
					}
				}
				$fixed201 = true;
			} else if ($error->code == 5) {
				$string = '<pm:template xmlns:pm="http://typeframe.com/pagemill">' . $string . '</pm:template>';
			}
		}
		return $string;
	}
	protected function _registerNamespaces() {
		foreach ($this->getNamespaces() as $prefix => $uri) {
			$this->registerXPathNamespace($prefix, $uri);
		}
	}
	protected static function _ConvertUtf8ToXmlEntities($string) {
		foreach (get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8') as $character => $entity) {
			if ( ($entity != '&amp;') && ($entity != '&quot;') && ($entity != '&gt;') && ($entity != '&lt;') ) {
				// Solution found at http://us3.php.net/ord (darien at etelos dot com 19-Jan-2007 12:27).
				$kbe = mb_convert_encoding($character, 'UCS-4BE', 'UTF-8');
				for ($i = 0; $i < mb_strlen($kbe, 'UCS-4BE'); ++$i)
				{
					$kbe2      = mb_substr($kbe, $i, 1, 'UCS-4BE');
					$ord       = unpack('N', $kbe2);
					//$entities .= sprintf('<!ENTITY %s "&#%s;">', substr($v, 1, -1), $ord[1]);
				}
				$string = str_replace($character, '&#' . $ord[1] . ';', $string);
				$string = str_replace($entity, '&#' . $ord[1] . ';', $string);
			}
		}
		return $string;
	}
	public function asXml($filename = null) {
		$xml = parent::asXml();
		if ($this->_defaultNamespace()) {
			$xml = self::_InsertNamespace($this->_defaultNamespace(), $xml);
		}
		if ($filename) {
			$result = file_put_contents($filename, $xml);
			return ($result !== false);
		}
		return $xml;
	}
}
