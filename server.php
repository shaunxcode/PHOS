<?php
require_once('common.inc');

class PHOS_Server {
	private function trimContents($file) {
		return trim(file_get_contents($file));
	}
	
	function getClasses() {
		$classes = array();
		
		$packageDir = fileName(PACKAGEDIR);
		foreach(glob("$packageDir/*") as $package) {
			$packageName = array_pop(explode('/', $package));
			foreach(glob(fileName($package, CLASSDIR, '*')) as $class) {
				$className = array_pop(explode('/', $class));
				$classes[] = array(
					'package' => $packageName, 
					'class' => $className, 
					'parent' => trim(file_get_contents(fileName($class,'extend'))));
			}
			
			foreach(glob(fileName($package, MODULEDIR, '*')) as $module) {
				$moduleName = array_pop(explode('/', $module));
				$classes[] = array(
					'package' => $packageName,
					'module' => $moduleName,
					'parent' => trim(file_get_contents(fileName($module, 'extend'))));
			}
		}

		return $classes;		
	}
	
	function getClassDetails($args) {
		$base = fileName(PACKAGEDIR, str_replace('.', '/', $args->className));
		
		$details = array();
		foreach(array('extend', 'implement', 'mixin', 'description') as $detail) {
			$details[$detail] = $this->trimContents(fileName($base, $detail));
		}
		return $details;
	}
	
	function getClassMethods($args) {
		$base = fileName(PACKAGEDIR, str_replace('.', '/', $args->className), 'method');
		
		$methods = array();
		foreach(array('private', 'protected', 'public', 'static') as $scope) {
			foreach(glob(fileName($base, $scope, '*')) as $method) {			
				$methods[] = array(
					'name' => array_pop(explode('/', $method)), 
					'scope' => $scope);
			}
		}
		return $methods;
	}
	
	function getMethod($args) {
		$base = fileName(PACKAGEDIR, str_replace('.', '/', $args->methodName));
		return array(
			'body' => $this->trimContents($base));
	}
	
	function getClassProperties() {
		
	}
	
	function saveClassDetails() {
		
	}
	
	function saveClassProperties() {
		
	}
	
	function saveClassMethod() {
		
	}
}

$server = new PHOS_Server;
echo json_encode(array('result' => $server->{$_REQUEST['method']}((object)$_REQUEST)));