<?php
require_once ('common.inc');

class PHOS_PHP {
	public $constructorName = '__construct';
	
	public function startClass($class, $definition) {
		$string = "<?php\nclass $class";
		
		if($definition['extend']) {
			$string .= ' extends ' . pos($definition['extend']);
		}

		if($definition['implement']) {
			$string .= ' implements ' . implode(',', $definition['implement']);
		}
		
		return $string . "{\n";
	}

	public function addProperty($property) {
		return "\tprivate \${$property->name};\n"; 
	}
	
	public function selfAssign($property, $value) {
		return  "\t\t\$this->{$property->name} = $value";
	}
	
	public function newObject() {
		$args = func_get_args();
		$class = array_shift($args);
		return "new {$class}(" . implode(',', $args) . ");\n";
	}
	
	public function method($method) {
		return "\t{$method->scope} function {$method->name}{$method->args} {\n{$method->body}\n\t}\n\n";
	}
}

function loadMethods($classDir) {
	$classDir = fileName($classDir, METHODDIR);
	$methods = array();
	foreach(array('static', 'private', 'public', 'protected') as $methodScope) {
		$methodDir = fileName($classDir, $methodScope);
		if(file_exists($methodDir)) {
			foreach(glob(fileName($methodDir, '*')) as $method) {
				$name = array_pop(explode('/', $method));
				$body = explode("\n", trim(file_get_contents($method)));
				$args = $body[0][0] == '(' ? array_shift($body) : '()';
				
				//parse arg list to check for types and . rest args
				$si = 0; 
				$count = 0;
				$length = strlen($args);
				$argTokens = array();
				$token = '';
				$open = '(';
				$close = ')';
				$restArgs = false;
				$inString = false;
				$escapeNext = false;
				$escaped = false;
				while($si < $length) {
					$char = $args[$si++]; 

					if(!$inString && ($char == '"' || $char == "'")) {
						$inString = $char;
						$token .= $char;
						continue;
					}
					
					if($inString) {
						if($char == '\\') {
							$escapeNext = true;
						}
						
						$token .= $char;

						if(!$escaped && $char == $inString) {
							$inString = false;
						}
						
						if($escapeNext) {
							$escaped = true;
							$escapeNext = false;
						} else if($escaped) {
							$escaped = false;
						}
						
						continue;
					}
					
					if($char == $open) { 
						$count++;
						if($count > 1) {
							$token .= $char;
						}
					} else if($char == $close) {
						$count--;
						if($count == 0 && $restArgs) {
							$restArgs = trim($token);
						} else {
							$token .= $char;
						}
					} else if($count == 1 && $char == ',' || $char == '.') {
						$tparts = explode('=', $token);
						$argTokens[trim(array_shift($tparts))] = empty($tparts) ? false : implode('=', $tparts);
						if($char == '.') {
							$restArgs = true;	
						}
						$token = '';
					} else {
						$token .= $char;
				    }
				}

				if($restArgs) {
					$preBody = array("$restArgs = func_get_args();");
					foreach($argTokens as $argName => $default) {
						array_unshift($preBody, "$argName = " . ($default ? 'empty('. $restArgs.') ? ' . $default . ' : array_shift(' . $restArgs . ')' : 'array_shift('.$restArgs.')') . ';');
					}
					
					foreach($preBody as $line) {
						array_unshift($body, $line);
					}
					
					$args = '()';
				}
				
				$last = array_pop($body);
				if(!in_array(substr($last, -1, 1), array(';', '}'))) {
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
	return $methods;	
}

function loadProperties($classDir) {
	$properties = array();
	$propertyDir = fileName($classDir, PROPERTYDIR);
	if(file_exists($propertyDir)) {
		foreach(glob(fileName($propertyDir, '*')) as $property) {
			list($fullName, $type) = explode('.', $property);
			$name = array_pop(explode('/', $fullName));
			$properties[$name] = (object)array(
				'name' => $name, 
				'type' => $type, 
				'default' => trim(file_get_contents($property)));
		}
	}
	return $properties;	
}

function generateClass($package, $class, $language = 'PHP') {
	$languageClass = 'PHOS_' . $language;
	$language = new $languageClass;
	
	//make sure class exists
	$classDir = fileName(PACKAGEDIR, $package, CLASSDIR, $class);
	if(!file_exists($classDir)) {
		throw new Exception("$classDir does not appear to exist");
	}

	$definition = array('extend' => false, 'implement' => false, 'mixin' => false);
	foreach($definition as $dname => $val) {
		$dfile = fileName($classDir, $dname);
		if(file_exists($dfile)) {
			$definition[$dname] = array_map('trim', explode(',', trim(file_get_contents($dfile))));
		}
	}
	
	//get the properties, based on type build constructor
	$properties = loadProperties($classDir);
		
	//get the methods
	$methods = loadMethods($classDir);

	//start the file
	$file = $language->startClass($class, $definition);
	
	if($definition['mixin']) {
		//load mixin methods and properties
		foreach($definition['mixin'] as $module) {
			$moduleDir = fileName(PACKAGEDIR, $package, MODULEDIR, $module);
			$properties = array_merge($properties, loadProperties($moduleDir));
			$methods = array_merge($methods, loadMethods($moduleDir));
		}
	}
	
	//build the construct method based on the private propeties
	$constructor = '';
	foreach($properties as $property) {
		$file .= $language->addProperty($property);
		$constructor .= $language->selfAssign(
			$property, 
			$language->newObject($property->type, $property->default));
	}
	
	$file .= "\n";

	$methods[$language->constructorName] = (object)array(
		'name' => $language->constructorName, 
		'scope' => 'public', 
		'args' => '()', 
		'body' => $constructor);
	
	foreach($methods as $method) {
		$file .= $language->method($method);
	}
	
	
	//write file
	file_put_contents(fileName(GENERATEDIR, "class.$class.php"), $file . '}');
}

generateClass('SomeApplication', 'Person');
