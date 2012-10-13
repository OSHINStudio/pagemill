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
$x = new Pagemill_Data();
$x['name'] = 'Latte';
$x['price'] = '2.50';
$pm->setVariable('product', $x);
$html = <<<EOF
<html>
	<head>
		<title>My Page</title>
	</head>
	<body>
		<p pm:loop="people person">@{person->name}@</p>
		<p>
			The product is @{product->name}@ and the object is @{product}@
		</p>
		<pm:loop name="product" as="key value">
			<p>
				@{key}@ = @{value}@
			</p>
		</pm:loop>
		<pm:loop name="people" times="5" cycle="odd,even">
			@{loop_index + 1}@. @{name}@ (@{cycle}@)<br/>
		</pm:loop>
	</body>
</html>
EOF;

$pm->writeString($html);
var_dump($pm->data());
