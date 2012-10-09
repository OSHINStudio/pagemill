<?php
function pagemill_autoloader($classname) {
	$path = dirname(__FILE__) . '/classes/' . str_replace('_', '/', $classname) . '.php';
	if ( (file_exists($path)) && (is_file($path)) ) {
		require_once($path);
		return true;
	}
	return false;
}
spl_autoload_register("pagemill_autoloader");
