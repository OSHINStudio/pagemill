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
<html>
	<head>
		<title>@{title}@</title>
	</head>
	<body>
		<p><pm:attribute name="style">font-weight: bold;</pm:attribute>@{title}@@{body}@</p>
		<blockquote>
			<pm:if expr="1"><a href="http://www.google.com">Google</a></pm:if><pm:else>Not true</pm:else>
		</blockquote>
		<pm:loop name="people">its @{name}@ on loop @{loop_index}@<br/></pm:loop>
		<pm:for-each name="numbers">and a @{loop_value}@<br/></pm:for-each>
	</body>
</html>
EOF;
$pm->writeString($html);
/*$stream = new Pagemill_Stream();
echo $tree->process($pm->root(), $stream);*/
