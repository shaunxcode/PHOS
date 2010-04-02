<?php

define('CLASSDIR', 'class');

function fileName() {
	$args = func_get_args();
	return implode('/', $args);
}

function generateClass($class) {
	//make sure class exists
	$classDir = fileName(CLASSDIR, $class);
	if(!file_exists($classDir)) {
		throw new Exception("$classDir does not appear to exist");
	}
	
	//get the properties, based on type build constructor
	if(file_exists(fileName($classDir, 'property'))) {
		echo "HAS PROPERTIES";
	}
	
	//get static/class methods
	
	//get private methods
	
	//get public methods
	
	//write file
}

generateClass('Person');
