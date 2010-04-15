<?php
define('PACKAGEDIR', 'package');
define('CLASSDIR', 'class');
define('METHODDIR', 'method');
define('PROPERTYDIR', 'property');
define('GENERATEDIR', 'generated');
define('MODULEDIR', 'module');

function fileName() {
	$args = func_get_args();
	return implode('/', $args);
}