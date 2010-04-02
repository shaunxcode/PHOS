<?php

define('CLASSDIR', 'class');

function fileName() {
	$args = func_get_args();
	return $args
}

function generateClass($class) {
	//make sure class exists
	if(!file_exists(fileName(CLASSDIR, $class))) {
		throw new Exception("$class does not appear to exist");
	}
	
	//get the properties, based on type build constructor
	
	//get static/class methods
	
	//get private methods
	
	//get public methods
	
	//write file
}

generateClass('Person');
