<?php

class Pagemill_Doctype_Html extends Pagemill_Doctype {
	public function __construct($nsPrefix = '') {
		parent::__construct($nsPrefix);
	}
	public function entityReferences() {
		static $entities = null;
		if (is_null($entities)) {
			$entities = '';
			foreach (get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'UTF-8') as $k => $v)
			{
				// The following does not work with UTF-8.
				//$entities .= sprintf('<!ENTITY %s "&#%s;">', substr($v, 1, -1), ord($k));

				// Solution found at http://us3.php.net/ord (darien at etelos dot com 19-Jan-2007 12:27).
				$kbe = mb_convert_encoding($k, 'UCS-4BE', 'UTF-8');
				for ($i = 0; $i < mb_strlen($kbe, 'UCS-4BE'); ++$i)
				{
					$kbe2      = mb_substr($kbe, $i, 1, 'UCS-4BE');
					$ord       = unpack('N', $kbe2);
					$entities .= sprintf('<!ENTITY %s "&#%s;">', substr($v, 1, -1), $ord[1]);
				}
			}
		}
		return $entities;
	}
	public function encodeEntities($text) {
		
	}
	public function decodeEntities($text) {
		
	}
}
