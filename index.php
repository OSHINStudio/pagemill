<?php
require_once('autoload.php');

$pm = new Pagemill();
$pm->setVariable('title', 'My Page');
$loop = array();
$loop[] = array('name' => 'Bob');
$loop[] = array('name' => 'Joe');
$pm->setVariable('people', $loop);
$pm->setVariable('numbers', array('one', 'two', 'three'));
$pm->setVariable('check', 100);

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
			&ldquo;It's a hard-knock life.&rdquo;
		</p>
		<form>
			<label><input type="radio" name="check" value="99" pm:checked="@{check}@" />99</label>
			<label><input type="radio" name="check" value="100" pm:checked="@{check}@" />100</label>
			<select name="select" tmpl:selected="bar">
				<tmpl:loop name="people">
					<option value="@{name}@">@{name}@</option>
				</tmpl:loop>
				<option value="foo">Foo</option>
				<option value="bar">Bar</option>
			</select>
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
$html = '<div>@{check}@</div><div>Second</div><p>"@{people[0]->name}@"</p>';
$pm->writeString($html);
