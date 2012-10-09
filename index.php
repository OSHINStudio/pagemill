<?php
require_once('autoload.php');

$pm = new Pagemill();
$pm->setVariable('title', 'My Page');
$output = $pm->writeString('<!DOCTYPE html><p>@{title}@&copy;</p>');
echo $output;
