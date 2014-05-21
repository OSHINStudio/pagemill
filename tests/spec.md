# Pagemill Specifications

## Pagemill_SimpleXmlElement

An extension of PHP's SimpleXMLElement with features to simplify parsing of Pagemill templates and HTML documents.

* Automatically declare the Pagemill namespace if undeclared pm:* tags exist.
  Unlike the Parser, SimpleXmlElement does not inject the Pagemill namespace for expressions (@{foo}@).
  TestOfPagemillSimpleXmlElement::testPagemillNamespaceDeclaration
* LoadHtml() parses unbalanced HTML into well-formed XML.
  TestOfPagemillSimpleXmlElement::testLoadUnbalancedHtmlElement
* LoadHtml() parses HTML with undefined entities (e.g., bare ampersands) into well-formed XML.
  TestOfPagemillSimpleXmlElement::testLoadUndefinedHtmlEntity
* Convert well-formed fragments into Pagemill templates.
  TestOfPagemillSimpleXmlElement::testConversionOfFragment
* Read the inner content of an XML element as a string.
  TestOfPagemillSimpleXmlElement::testInnerXml
* Query a document with a CSS selector.
  TestOfPagemillSimpleXmlElement::testCssSelector
* Implicitly register the Pagemill namespace when using an XPath query on a Pagemill template.
  TestOfPagemillSimpleXmlElement::testXpathQueryOnPagemillTag

## Pagemill_Parser

The class that builds a tree of tags from an XML document.

* Parse a well-formed XML document.
  * TestOfPagemillParser::testParseWellFormedXmlDocument
* Parse a well-formed XML fragment as a tree with a root pm:template tag.
  * TestOfPagemillParser::testParseWellFormedXmlFragment
* Throw an exception on parsing a malformed XML document or fragment.
  * TestOfPagemillParser::testExceptionForMalformedXml

## Pagemill_Doctype

* Throw an exception on parsing a document with an undefined entity
  * TestOfPagemillDoctype::testExceptionForUndefinedEntity
* Register an entity for a doctype and parse a document that contains the entity
  * TestOfPagemillDoctype::testRegisterEntityAndParseinDocument

## Pagemill_Data

* Evaluate a variable.
  * TestOfPagemillData::testEvaluateVariable
* Evaluate a mathematical expression.
  * TestOfPagemillData::testEvaluateMath
* Register and evaluate a function.
  * TestOfPagemillData::testRegisterAndEvaluateFunction
* Fork inherits parent values.
  * TestOfPagemillData::testForkInheritsParentValues
* Adding a value to a tine does not add it to the parent.
  * TestOfPagemillData::testForkHasLocalScope
* Modifying a parent's value in the tine modifies it in the parent.
  * TestOfPagemillData::testForkValueDoesNotChangeParentValue
