<?php
require_once('autoload.php');

$pm = new Pagemill();
$pm->setVariable('title', 'My Page');
$loop = array();
$loop[] = array('name' => 'Bob');
$loop[] = array('name' => 'Joe');
$pm->setVariable('people', $loop);
$pm->setVariable('numbers', array('one', 'two', 'three'));

// <!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
$html = <<<EOF
<!DOCTYPE html>
<html xmlns:tmpl="http://typeframe.com/pagemill">
	<head>
		<title>@{title}@</title>
	</head>
	<body>
		<div id="foobar"></div>
		<p><tmpl:attribute name="style">font-weight: bold;</tmpl:attribute>@{title}@@{body}@</p>
		<blockquote>
			<tmpl:if expr="1"><a href="http://www.google.com">Google</a></tmpl:if><tmpl:else>Not true</tmpl:else>
		</blockquote>
		<tmpl:for-each name="numbers">and a @{loop_value}@<br/></tmpl:for-each>
		<ul>
			<li tmpl:loop="people">@{name}@ in a list</li>
		</ul>
		<p>
			"It's a hard-knock life."
		</p>
		<form>
			<tmpl:select name="select" selected="baz">
				<option value="foo">Foo</option>
				<option value="bar">Bar</option>
				<option value="baz">Baz</option>
			</tmpl:select>
		</form>
		&ldquo;The count is @{count(people)}@&rdquo;
		<tmpl:if expr="begins('USA', 'U')">Yes!</tmpl:if>
		<tmpl:choose>
			<tmpl:when expr="1 == 2">what</tmpl:when>
			<tmpl:when expr="count(people) == 2">correct</tmpl:when>
			<tmpl:otherwise>none are true</tmpl:otherwise>
		</tmpl:choose>
	</body>
</html>
EOF;
$pm->writeString($html);
/*$stream = new Pagemill_Stream();
echo $tree->process($pm->root(), $stream);*/
