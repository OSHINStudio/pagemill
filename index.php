<?php
require_once('autoload.php');

class Pagemill_Doctype_Typeframe extends Pagemill_Doctype_Template {
	public function __construct($nsPrefix) {
		parent::__construct($nsPrefix);
		$this->registerTag('html', 'Pagemill_Tag_Typef_Html');
		$this->registerTag('head', 'Pagemill_Tag_Typef_Head');
		$this->registerTag('body', 'Pagemill_Tag_Typef_Body');
		$this->registerTag('import', 'Pagemill_Tag_Typef_Import');
		$this->registerTag('calendar', 'Pagemill_Tag_Typef_Calendar');
		$this->registerTag('codeblock', 'Pagemill_Tag_Codeblock');
		$this->registerTag('editor', 'Pagemill_Tag_Editor');
		$this->registerTag('fileupload', 'Pagemill_Tag_FileUpload');
		$this->registerTag('imageupload', 'Pagemill_Tag_ImageUpload');
		$this->registerTag('socket', 'Pagemill_Tag_Socket');
		$this->registerTag('debug', 'Pagemill_Tag_Typef_Debug');
		
		$this->registerAttribute('thumb', 'Pagemill_Attribute_Typef_Thumbnail');
	}
}

class Pagemill_Tag_Socket extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$stream->append("YES! I'm in here!");
	}
}

class Pagemill_TagPreprocessor_Typef_Thumbnail extends Pagemill_TagPreprocessor {
	private $_ratio;
	public function __construct($ratio = true) {
		$this->_ratio = $ratio;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		$base = '/home/fred/public_html/pagemill/';
		$src = $data->parseVariables($tag->getAttribute('src'));
		$attr = 'src';
		if (!$src) {
			$src = $data->parseVariables($tag->getAttribute('href'));
			$attr = 'href';
		}
		$width = $data->parseVariables($tag->getAttribute('width'));
		$height = $data->parseVariables($tag->getAttribute('height'));
		// TODO: Is this good enough?
		$file = $base . $src;
		$md5 = md5("{$src}_{$width}_{$height}_{$this->_ratio}");
		if (file_exists("{$base}timg/{$md5}")) {
			if (filemtime($file) < filemtime($base.'timg/'.$md5)) {
				$tag->setAttribute($attr, "timg/{$md5}");
				$tag->removeAttribute('width');
				$tag->removeAttribute('height');
				return;
			}
		}
		// TODO: Is a temporary image a good idea?
		//$this->setAttribute('src', 'pending.png');
		// TODO: Schedule the image for resizing (or resize it here if there's
		// no task for it?)
		// Even better: resize it in place if the file is below a particular
		// size! We'll try it with 900kb for now.
		/*if (filesize($file) < 900000) {
			Gdi::Thumbnail($file, $base.'timg/'.$md5, $width, $height, $this->_ratio);
			$tag->setAttribute($attr, "timg/{$md5}");
			$tag->removeAttribute('width');
			$tag->removeAttribute('height');
		} else {
			// TODO: Schedule the resizing.
		}*/
	}
}

class Pagemill_Attribute_Typef_Thumbnail extends Pagemill_Attribute_Hidden {
	public function __construct($name, $value, Pagemill_Tag $tag) {
		parent::__construct($name, $value, $tag);
		if (strtolower($this->value) != 'fixed' && strtolower($this->value) != 'ratio') {
			throw new Exception("Value of {$name} must be 'fixed' or 'ratio'");
		}
		$ratio = (strtolower($this->value) == 'ratio');
		$tag->attachPreprocess(new Pagemill_TagPreprocessor_Typef_Thumbnail($ratio));
	}
}

class Pagemill_TagPreprocessor_Typef_Export extends Pagemill_TagPreprocessor {
	private $_name;
	private $_tag;
	private static $_exports = array();
	public function __construct($name, Pagemill_Tag $tag) {
		$this->_name = $name;
		$this->_tag = $tag;
	}
	public function process(Pagemill_Tag $tag, Pagemill_Data $data, Pagemill_Stream $stream) {
		self::$_exports[$this->_name] = $this->_tag;
	}
	public static function Export($name) {
		return (isset(self::$_exports[$name]) ? self::$_exports[$name] : null);
	}
}

class Pagemill_Tag_Typef_Html extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
	}
	public function output(\Pagemill_Data $data, \Pagemill_Stream $stream) {
		$this->name = 'html';
		$pm = new Pagemill($data);
		$skinTree = $pm->parseFile('skin.html');
		$skinTree->process($data, $stream);
	}
}

class Pagemill_Tag_Typef_Import extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$top = $parent;
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$import = Pagemill_TagPreprocessor_Typef_Export::Export($this->getAttribute('name'));
		if ($import) {
			foreach ($import->children() as $child) {
				$child->process($data, $stream);
			}
		} else {
			throw new Exception('Exported tag not found');
		}
	}
}

class Pagemill_Tag_Typef_Head extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->parent()->attachPreprocess(new Pagemill_TagPreprocessor_Typef_Export('head', $this));
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->name = 'head';
		parent::output($data, $stream);
	}
}

class Pagemill_Tag_Typef_Body extends Pagemill_Tag {
	public function __construct($name, array $attributes = array(), Pagemill_Tag $parent = null, Pagemill_Doctype $doctype = null) {
		parent::__construct($name, $attributes, $parent, $doctype);
		$this->parent()->attachPreprocess(new Pagemill_TagPreprocessor_Typef_Export('body', $this));
	}
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$this->name = 'body';
		parent::output($data, $stream);
	}
}

class Pagemill_Tag_Typef_Debug extends Pagemill_Tag {
	public function output(Pagemill_Data $data, Pagemill_Stream $stream) {
		$debug = new Pagemill_Data();
		$debug->set('data', $data);
		$pm = new Pagemill();
		$tree = $pm->parseFile('debug.inc.html');
		$tree->process($debug, $stream);
		return;
		$this->_recurse($data, $stream);
	}
	private function _recurse($data, Pagemill_Stream $stream) {
		foreach($data as $k => $v) {
			if (is_array($v) || $v instanceof Iterator) {
				$stream->append("{$k} => (object)<br/>");
				$this->_recurse($v, $stream);
			} else {
				$stream->append("{$k} => {$v}<br/>");
			}
		}		
	}
}

Pagemill_Doctype::SetTemplateDoctypeClass('Pagemill_Doctype_Typeframe');

class Weird {
	private $_value;
	public function __construct($value) {
		$this->_value = $value;
	}
	public function value() {
		return $this->_value;
	}
}

function weirdConversion(Weird $object) {
	$data = new Pagemill_Data();
	$data['value'] = $object->value();
	return $data;
}
Pagemill_Data::ClassHandler('Weird', 'weirdConversion');

$pm = new Pagemill();
$pm->setVariable('title', 'My Page');

$weird = new Weird('something weird');
$pm->setVariable('weird', $weird);

$people = array();
$people[] = array(
	'name' => 'Steve',
	'title' => 'Janitor'
);
$people[] = array(
	'name' => 'Bob',
	'title' => 'Salesman',
	'phones' => array(
		array(
			'number' => '555-5555'
		)
	)
);
$people[] = array(
	'name' => 'Joe',
	'title' => 'Developer'
);
$pm->setVariable('people', $people);

$pm->writeFile('template.html');
