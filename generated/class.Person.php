<?php
class Person extends Model implements a,b,c{
	private $age;
	private $height;
	private $items;
	private $weight;
	private $hours;
	private $minutes;
	private $seconds;

	static function getHeights() {
		return array('large', 'small');
	}

	private function consume($item) {
		$this->items->push($item);
		return $this;
	}

	public function eat($item1, $item2) {
		return $this->consume($item1)
		            ->consume($item2);
	}

	public function new($name, $age, $height) {
		$this->name = $name;
		$this->age = $age;
		$this->height = $height;
	}

	public function restOther() {
		$z = func_get_args();
		$x = array_shift($z);
		$y = array_shift($z);
		foreach($z as $id => $val) {
			echo "$id is $val";
		}
	}

	public function restTest() {
		$rest = func_get_args();
		$a = array_shift($rest);
		$b = empty($rest) ?  array('a','b','c' => 'def') : array_shift($rest);
		$c = empty($rest) ?  (5 + (6 + 7)) : array_shift($rest);
		$d = empty($rest) ?  "( \" " : array_shift($rest);
		$e = empty($rest) ?  '( \' )'   : array_shift($rest);
		echo $a * $b;
		print_r($rest);
	}

	public function sayItems() {
		echo $this->items->implode(', ');
		return $this;
	}

	protected function test($x, $y) {
		return $x[$y];
	}

	public function sin($x) {
		return sin($x);
	}

	public function difference($a, $b) {
		return $a - $b;
	}

	public function __construct() {
		$this->age = new Second(300);
		$this->height = new Inch();
		$this->items = new List("one element", "two element", "three");
		$this->weight = new Pound();
		$this->hours = new Int(60 * 60);
		$this->minutes = new Int(60);
		$this->seconds = new Int(1);

	}

}