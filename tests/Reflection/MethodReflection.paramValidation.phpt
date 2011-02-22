<?php

/**
 * Test: MethodReflection tests.
 *
 * @author     Jan Tvrdík
 * @package    Nette\Reflection
 * @subpackage UnitTests
 */

use Nette\Reflection\MethodReflection;
use Nette\Reflection\ClassReflection;


require __DIR__ . '/../bootstrap.php';


class A
{
	/**
	 * @param   string|int|TRUE|NULL
	 * @param   bool $b optional comment
	 * @param   int|float $c
	 * @param   mixed another comment
	 * @param   int|mixed
	 * @param   integer | mixed
	 */
	public function foo($a, $b, $c, $d, $e) {}

	public function noAnnotation($a, $b) {}

	/** @param int */
	public function int($a)
	{
		Assert::true(gettype($a) === 'integer');
	}

	/** @param float */
	public function float($a)
	{
		Assert::true(gettype($a) === 'double');
	}

	/** @param int|float */
	public function intOrFloat($a)
	{
		Assert::true(gettype($a) === 'integer' || gettype($a) === 'double');
	}

	/** @param float|int */
	public function floatOrInt($a)
	{
		Assert::true(gettype($a) === 'integer' || gettype($a) === 'double');
	}

	/** @param string */
	public function string($a)
	{
		Assert::true(gettype($a) === 'string');
	}

	/** @param bool */
	public function bool($a)
	{
		Assert::true(gettype($a) === 'boolean');
	}

	/** @param array */
	public function arrayTest($a)
	{
		Assert::true(gettype($a) === 'array');
	}

	/** @param mixed */
	public function mixed($a)
	{

	}

	/** @param int|NULL */
	public function intOrNull($a)
	{
		Assert::true(gettype($a) === 'integer' || $a === NULL);
	}
}

foreach (ClassReflection::from('A')->getMethods() as $methodReflection) {
	$reflections[$methodReflection->getName()] = $methodReflection;
}


Assert::same(array(
	array('string', 'integer', 'true', 'null'),
	array('boolean'),
	array('integer', 'double'),
	NULL,
	NULL,
	array('integer'),
), $reflections['foo']->getAllowedParametersTypes());

Assert::same(array(), $reflections['noAnnotation']->getAllowedParametersTypes());

$object = new A();

$i = 123;
$is = '123';
$in = -123;
$isn = '-123';
$f = 123.4;
$fs = '123.4';
$s = 'foo';
$n = NULL;
$b = TRUE;
$o = new stdClass();
$a = array();

$validTestData = array(
	'int' => array($i, $is, $in, $isn),
	'float' => array($is, $isn, $f, $fs),
	'intOrFloat' => array($i, $is, $in, $isn, $f, $fs),
	'floatOrInt' => array($i, $is, $in, $isn, $f, $fs),
	'string' => array($s),
	'bool' => array($b, '1', '0'),
	'arrayTest' => array($a),
	'mixed' => array($i, $f, $s, $n, $b, $o, $a),
	'intOrNull' => array($i, $n),
);

$invalidTestData = array(
	'int' => array($s, $n, $b, $o, $a, $f, $fs, 1.0, '1.0'),
	'float' => array($s, $n, $b, $o, $a, $i),
	'intOrFloat' => array($s, $n, $b, $o, $a),
	'floatOrInt' => array($s, $n, $b, $o, $a),
	'string' => array($n, $b, $o, $a, $i, $f),
	'bool' => array(0, 1, '2'),
	'arrayTest' => array($i, $f, $s, $n, $b, $o),
);

foreach ($validTestData as $method => $data) {
	foreach ($data as $value) {
		try {
			$reflections[$method]->invokeNamedArgs($object, array('a' => $value), TRUE);
		} catch (Exception $e) {
			Assert::fail('Validation failed for method ' . $method);
		}
	}
}

foreach ($invalidTestData as $method => $data) {
	foreach ($data as $value) {
		try {
			$reflections[$method]->invokeNamedArgs($object, array('a' => $value), TRUE);
			Assert::fail('Validation failed for method ' . $method);
		} catch (Exception $e) {

		}
	}
}