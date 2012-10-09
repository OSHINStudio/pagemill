<?php

class Pagemill_Doctype implements Pagemill_DoctypeInterface {
	private static $_doctypes = array();
	private static $_extensions = array();
	public function entityReferences() {
		return '';
	}
	public function encodeEntities($text) {
		return $text;
	}
	public function decodeEntities($text) {
		return $text;
	}
	public function tagRegistry() {
		return array();
	}
	public static function ForFile($filename) {
		$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$cls = (isset(self::$_extensions[$extension]) ? self::$_extensions[$extension] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls();
	}
	public static function ForDoctype($doctype) {
		$cls = (isset(self::$_doctypes[$doctype]) ? self::$_doctypes[$doctype] : 'Pagemill_Doctype');
		if ($cls != 'Pagemill_Doctype' && !is_subclass_of($cls, 'Pagemill_Doctype')) {
			throw new Exception("Doctype class must be a subclass of Pagemill_Doctype");
		}
		return new $cls();		
	}
	public static function RegisterDoctype($root, $class) {
		self::$_doctypes[$root] = $class;
	}
	public static function RegisterFileExtension($extension, $class) {
		self::$_extensions[strtolower($extension)] = $class;
	}
}

Pagemill_Doctype::RegisterDoctype('html', 'Pagemill_Doctype_Html');
