# Pagemill

Pagemill is an extensible XML-based templating engine.

## How to Use Pagemill

    <?php
	require('/path/to/autoload.php');
	$pagemill = new Pagemill();
	$pagemill->setVariable('title', 'My Page');
	$pagemill->setVariable('content', 'Hello, world!');
	$pagemill->writeText('<h1>@{title}@</h1><p>@{content}@</p>');

## Template Syntax

There are three aspects of templates: expressions, tags, and attributes.

### Expressions

Pagemill uses expressions to render variable data. The simplest form of an expression is `@{variable_name}@`.

Expressions can also contain operations and functions, e.g, `@{1 + 1}@` or `@{firstname . ' ' . lastname}@`.

Expressions can access objects and arrays using standard PHP notation, e.g., `@{person->name}@` or `@{items[0]}@`.

#### Expression Functions

A variety of functions are available in Pagemill expressions. Most of them are equivalent to PHP functions. Examples:
* @{round(number)}@
* @{count(array)}@
* @{uppercase(text)}@

### Tags

A Pagemill Tag is an XML element that is mapped to a Pagemill_Tag subclass for special processing. By default, tags
use the pm namespace (e.g., `<pm:tag_name>`).

#### pm:if

The pm:if tag uses a condition to determine whether to render its content. If the condition is true, it outputs to the document. There is also a pm:else tag to handle the opposite condition.

    <pm:if expr="age >= 18">
	    You're old enough to vote!
	</pm:if>
	<pm:else>
		You need to wait @{18 - age}@ years before you can vote.
	</pm:else>

#### pm:loop

The pm:loop tag iterates over arrays. Given the following PHP code:

    $people = array();
	$people[] = array('name' => 'Bob');
	$people[] = array('name' => 'Joe');
	$people[] = array('name' => 'Sue');
	$pagemill->setVariable('people', $people);
	
You can render the names with the following template:

    <pm:loop name="people">
	    @{name}@
	</pm:loop>

You can also specify an object name for each value:

    <pm:loop name="people" as="person">
	    @{person->name}@
	</pm:loop>

### Attributes

Pagemill supports several XML attributes for processing as well, including shortcuts for the pm:if and pm:loop tags.

    <p pm:if="age >= 18">You will only see this paragraph if you are old enough to vote.</p>

    <p pm:loop="people">Displaying @{name}@ in a paragraph.</p>

	<p pm:loop="people person">Displaying @{person->name}@ in a paragraph.</p>

## License

Pagemill is released under the [MIT License](http://opensource.org/licenses/MIT).

Copyright (c) 2010-2013 [Blind Acre, Inc.](http://blindacre.com)
