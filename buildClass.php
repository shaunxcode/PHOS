<?php

define('CLASSDIR', 'class');
define('GENERATEDIR', 'generated');

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
	$properties = array();
	$propertyDir = fileName($classDir, 'property');
	if(file_exists($propertyDir)) {
		foreach(glob(fileName($propertyDir, '*')) as $property) {
			list($fullName, $type) = explode('.', $property);
			$name = array_pop(explode('/', $fullName));
			$properties[$name] = (object)array(
				'name' => $name, 
				'type' => $type, 
				'default' => file_get_contents($property));
		}
	}
		
	//get the methods
	$methods = array();
	foreach(array('static', 'private', 'public') as $methodScope) {
		$methodDir = fileName($classDir, $methodScope);
		if(file_exists($methodDir)) {
			foreach(glob(fileName($methodDir, '*')) as $method) {
				$name = array_pop(explode('/', $method));
				$body = explode("\n", trim(file_get_contents($method)));
				$args = $body[0][0] == '(' ? array_shift($body) : '()';
				
				$last = array_pop($body);
				if(substr($last, -1, 1) !== ';') {
					$last .= ';';
				}
				$body[] = $last;
				
				foreach($body as $ln => $line) {
					$body[$ln] = "\t\t$line";
				}
				
				$methods[$name] = (object)array(
					'name' => $name, 
					'scope' => $methodScope, 
					'args' => $args,
					'body' => implode("\n", $body));
			}
		}
	}

	$file = "<?php\nclass $class {\n";
	
	//build the construct method based on the private propeties
	$constructor = '';
	foreach($properties as $property) {
		$file .= "\tprivate {$property->name};\n";
		if(!empty($property->default)) {
			$constructor .= "\t\t\$this->{$property->name} = {$property->default};\n";
		}
	}
	
	$file .= "\n";

	$methods['__construct'] = (object)array(
		'name' => '__construct', 
		'scope' => 'public', 
		'args' => '()', 
		'body' => $constructor);
	
	foreach($methods as $method) {
		$file .= "\t{$method->scope} function {$method->name}{$method->args}{\n{$method->body}\n\t}\n\n";
	}
	
	//write file
	file_put_contents(fileName(GENERATEDIR, "class.$class.php"), $file . '}');
}

generateClass('Person');