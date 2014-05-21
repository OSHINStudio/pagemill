<?php
require_once('../autoload.php');
require_once('simpletest/autorun.php');

class TestOfPagemillSimpleXmlElement extends UnitTestCase {
	public function testLoadUnbalancedHtmlElement() {
		$html = '<p>Paragraph 1<p>Paragraph 2';
		$xml = Pagemill_SimpleXmlElement::LoadHtml($html);
		$paras = $xml->xpath('//p');
		$this->assertTrue(count($paras) == 2, "Failed to parse unbalanced paragraphs");
	}
	public function testLoadUndefinedHtmlEntity() {
		$html = '<html><body>This & That</body></html>';
		$xml = Pagemill_SimpleXmlElement::LoadHtml($html);
		$this->assertTrue($xml->body == 'This & That', "Failed to parse bare ampersand");
		//$html = '<html><body>&ldquo;Quoted&rdquo;</body></html>';
		//$xml = Pagemill_SimpleXmlElement::LoadHtml($html);
		//$this->assertTrue($xml->body->asXml() == '<body>&ldquo;Quoted&rdquo;</body>', 'Failed to parse HTML entities');
	}
	public function testPagemillNamespaceDeclaration() {
		// If the code contains Pagemill tags (pm:*) or expressions (@{expr}@) without
		// declaring the namespace, Pagemill_SimpleXmlElement should add the declaration
		// Detect from tag
		$code = '<html><body><pm:loop name="foo">@{bar}@</pm:loop></body></html>';
		$xml = Pagemill_SimpleXmlElement::LoadString($code);
		$ns = $xml->getDocNamespaces();
		$this->assertTrue(isset($ns['pm']), "Failed implicit declaration of Pagemill namespace from tag");
		$loops = $xml->xpath('//pm:loop');
		$this->assertTrue(count($loops) == 1, "Failed to parse Pagemill tag after implicit namespace declaration");
	}
	public function testConversionOfFragment() {
		// If the code is a well-formed document fragment, convert it into a template
		// with pm:template as the root.
		$code = "<p>Paragraph one</p><p>Paragraph two</p>";
		$xml = Pagemill_SimpleXmlElement::LoadString($code);
		$this->assertTrue($xml, "Failed to convert a well-formed fragment into a template");
		$ns = $xml->getDocNamespaces();
		$this->assertTrue(isset($ns['pm']), "Conversion of well-formed fragment did not declare Pagemill namespace");
	}
	public function testInnerXml() {
		$code = '<div><p>inner</p></div>';
		$xml = Pagemill_SimpleXmlElement::LoadString($code);
		$this->assertTrue($xml->innerXml() == '<p>inner</p>', 'Failed to read inner XML of element');
	}
	public function testCssSelector() {
		$code = '<div><p class="para" id="first">First Paragraph</p><p class="para">Second Paragraph</p></div>';
		$xml = Pagemill_SimpleXmlElement::LoadString($code);
		$this->assertTrue(count($xml->select('div')) == 1, 'Failed to select parent div');
		$this->assertTrue(count($xml->select('p')) == 2, 'Failed to select paragraphs');
		$this->assertTrue(count($xml->select('.para')) == 2, 'Failed to select class');
		$this->assertTrue(count($xml->select('#first')) == 1, 'Failed to select ID');
	}
	public function testXpathQueryOnPagemillTag() {
		// Implicitly register the Pagemill namespace when using an XPath query on a Pagemill template.
		$code = '<div><pm:tag>foobar</pm:tag></div>';
		$xml = Pagemill_SimpleXmlElement::LoadString($code);
		$this->assertTrue(count($xml->xpath('pm:tag')) == 1, 'Failed to detect Pagemill element in XPath query');
	}
}

/*class TestOfPagemillParser extends UnitTestCase {
	public function testDetectDefaultNamespaceByRootElement() {
		// Detect HTML
		$parser = new Pagemill_Parser();
		$tree = $parser->parse('<html><head></head><body><p>foo</p></body></html>');
		$ns = $tree->doctype()->getPrefixFor('http://www.w3.org/1999/xhtml');
		$this->assertTrue($ns === '', "HTML doctype was not detected by root element");
		// Detect arbitrary doctype
		Pagemill_Doctype::RegisterDoctype('test_root', 'Pagemill_Doctype_Text');
		$tree = $parser->parse('<test_root><example/></test_root>');
		$this->assertTrue($tree->doctype() instanceof Pagemill_Doctype_Text, "Arbitrary doctype was not detected by root element");
	}
	public function testPagemillNamespaceDeclaration() {
		// If the code contains Pagemill tags (pm:*) without declaring the
		// namespace, Pagemill_Parser should add the declaration
		$code = '<html><body><pm:loop name="foo">@{bar}@</pm:loop></body></html>';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code);
		$ns = $tree->doctype()->getPrefixFor('http://typeframe.com/pagemill');
		$this->assertTrue($ns !== false, "Parser did not implicitly declare Pagemill namespace");
		// Make sure the root doctype is intact
		$ns = $tree->doctype()->getPrefixFor('http://www.w3.org/1999/xhtml');
		$this->assertTrue($ns === '', "HTML doctype was not detected by root element");
	}
}*/

class TestOfPagemillParser extends UnitTestCase {
	public function testParseWellFormedXmlDocument() {
		$code = '<root><node>foo</node></root>';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code);
		$this->assertTrue(is_a($tree, 'Pagemill_Tag'), "Failed to parse document into Pagemill tag tree");
		$this->assertTrue($tree->name() == 'root', "Tag tree does not have correct root element");
	}
	public function testParseWellFormedXmlFragment() {
		$code = '<node>foo</node><node>bar</node>';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code);
		$this->assertTrue(is_a($tree, 'Pagemill_Tag'), "Failed to parse fragment into Pagemill tag tree");
		$this->assertTrue($tree->name() == 'pm:template', "Tag tree did not make pm:template the root element of a fragment");
	}
	public function testExceptionForMalformedXml() {
		$this->expectException();
		$code = '<root>foo';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code);
	}
}

class TestOfPagemillDoctype extends UnitTestCase {
	public function testExceptionForUndefinedEntity() {
		$this->expectException();
		$doctype = new Pagemill_Doctype('');
		$code = '<node>foo &entity; bar</node>';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code, $doctype);
	}
	public function testRegisterEntityAndParseInDocument() {
		$doctype = new Pagemill_Doctype('');
		$doctype->addEntity('entity', '&entity;');
		$code = '<node>foo &entity; bar</node>';
		$parser = new Pagemill_Parser();
		$tree = $parser->parse($code, $doctype);		
	}
}


class TestOfPagemillData extends UnitTestCase {
	public function testEvaluateVariable() {
		$data = new Pagemill_Data();
		$data['foo'] = 'bar';
		$this->assertTrue($data->evaluate('foo') == 'bar', 'Data variable was not evaluated correctly');
		$this->assertTrue($data->parseVariables('bar == @{foo}@') == 'bar == bar', 'Data variable in code was not evaluated correctly');
	}
	public function testEvaluateMath() {
		$data = new Pagemill_Data();
		$this->assertTrue($data->evaluate('1 + 1') == '2', 'Mathematical expression was not evaluated correctly');
		$this->assertTrue($data->parseVariables('@{1 + 1}@ == 2') == '2 == 2', 'Mathematical expression in code was not evaluated correctly');
	}
	public static function ExampleFunction() {
		return 'bar';
	}
	public function testRegisterAndEvaluateFunction() {
		Pagemill_Data::RegisterExprFunc('foo', 'TestofPagemillData::ExampleFunction');
		$data = new Pagemill_Data();
		$this->assertTrue($data->evaluate('foo()') == 'bar', 'Registered function was not evaluated correctly');
		$this->assertTrue($data->parseVariables('@{foo()}@ == bar') == 'bar == bar', 'Registered function in code was not evaluated correctly');
	}
	public function testForkInheritsParentValues() {
		$data = new Pagemill_Data();
		$data['foo'] = 'bar';
		$fork = $data->fork();
		$this->assertTrue($fork['foo'] == 'bar', 'Fork did not inherit parent data');
	}
	public function testForkHasLocalScope() {
		$data = new Pagemill_Data();
		$fork = $data->fork();
		$fork['foo'] = 'bar';
		$this->assertFalse(isset($data['foo']), 'Value in fork\'s local scope bled into its parent');
	}
	public function testForkValueDoesNotChangeParentValue() {
		$data = new Pagemill_Data();
		$data['foo'] = 'bar';
		$fork = $data->fork();
		$fork['foo'] = 'baz';
		$this->assertFalse($data['foo'] == 'baz', 'Modified value in fork changed value in parent');
	}
}

class TestOfPagemillTag extends UnitTestCase {
	public function testOutputSelfTerminatingElementWithAttribute() {
		$tag = new Pagemill_Tag('my_tag', array('my_attribute' => 'my_value'), null, null);
		$data = new Pagemill_Data();
		$stream = new Pagemill_Stream(true);
		$tag->process($data, $stream);
		$output = $stream->clean();
		$xml = Pagemill_SimpleXmlElement::LoadString($output);
		$this->assertTrue($xml->getName() == 'my_tag', 'Tag did not process correct tag name');
		$this->assertTrue($xml['my_attribute'] == 'my_value', 'Tag did not process correct attribute name/value');
	}
}
