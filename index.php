<?php
require_once('autoload.php');

$pm = new Pagemill();
$pm->setVariable('title', 'My Page');
$html = <<<EOF
<!DOCTYPE html>
<html>
	<head>
		<title>@{title}@</title>
	</head>
	<body>
		<p><pm:attribute name="style">font-weight: bold;</pm:attribute>@{title}@<u>&copy;</u>@{body}@</p>
		<blockquote>
			<pm:if expr="1"><a href="http://www.google.com">Google</a></pm:if><pm:else>Not true</pm:else>
		</blockquote>
	</body>
</html>
EOF;
$pm->writeString($html);
/*$stream = new Pagemill_Stream();
echo $tree->process($pm->root(), $stream);*/
