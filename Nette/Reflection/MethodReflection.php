<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004, 2011 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\Reflection;

use Nette,
	Nette\ObjectMixin;



/**
 * Reports information about a method.
 *
 * @author     David Grudl
 */
class MethodReflection extends \ReflectionMethod
{

	/**
	 * @param  string|object
	 * @param  string
	 * @return Nette\Reflection\MethodReflection
	 */
	public static function from($class, $method)
	{
		return new static(is_object($class) ? get_class($class) : $class, $method);
	}



	/**
	 * @return array
	 */
	public function getDefaultParameters()
	{
		$res = array();
		foreach (parent::getParameters() as $param) {
			$res[$param->getName()] = $param->isDefaultValueAvailable()
				? $param->getDefaultValue()
				: NULL;

			if ($param->isArray()) {
				settype($res[$param->getName()], 'array');
			}
		}
		return $res;
	}



	/**
	 * Invokes method using named parameters.
	 * @param  object
	 * @param  array
	 * @param  bool perform validation?
	 * @return mixed
	 */
	public function invokeNamedArgs($object, $args, $validate = FALSE)
	{
		if ($validate) {
			$types = $this->getAllowedParametersTypes();
			Nette\Debug::barDump($types, 'Allowed types');
		}

		$params = parent::getParameters();
		$res = array();
		$i = 0;
		foreach ($params as $param) {
			$name = $param->getName();
			if (isset($args[$name])) { // NULL treats as none value
				$value = $args[$name];
				if ($validate && isset($types[$i])) {
					$type = gettype($value);
					$valid = FALSE;
					$converted = FALSE;
					foreach ($types[$i] as $allowedType) {
						if ($type === 'string') {
							$converted = TRUE;
							if ($allowedType === 'integer' && $value === (string)(int) $value) {
								$value = (int) $value;
							} elseif ($allowedType === 'double' && is_numeric($value)) {
								$value = (float) $value;
							} elseif ($allowedType === 'boolean' && ($value === '1' || $value === '0')) {
								$value = (bool) $value;
							} elseif ($allowedType === 'true' && $value === '1') {
								$value = TRUE;
							} elseif ($allowedType === 'false' && $value === '0') {
								$value = FALSE;
							} else {
								$converted = FALSE;
							}
						} elseif ($type === 'integer') {
							$converted = TRUE;
							if ($allowedType === 'double') {
								$value = (float) $value;
							} elseif ($allowedType === 'string') {
								$value = (string) $value;
							} else {
								$converted = FALSE;
							}
						} elseif ($type === 'double') {
							if ($allowedType === 'string') {
								$value = (string) $value;
								$converted = TRUE;
							}
						}

						if ($converted || $type === $allowedType || ($allowedType === 'true' && $value === TRUE) || ($allowedType === 'false') && $value === FALSE) {
							$valid = TRUE;
							break;
						}
					}
					if (!$valid) throw new \InvalidArgumentException("Invalid value for parameter $name. Expected " . implode(' or ', $types[$i]));
				}
				$res[$i++] = $value;

			} else {
				if ($param->isDefaultValueAvailable()) {
					$res[$i++] = $param->getDefaultValue();
				} elseif ($validate && isset($types[$i]) && !in_array('null', $types[$i])) {
					throw new \InvalidArgumentException("Parameter $name is required!");
				} else {
					$res[$i++] = NULL;
				}
			}
		}
		return $this->invokeArgs($object, $res);
	}



	/**
	 * @return Nette\Callback
	 */
	public function getCallback()
	{
		return new Nette\Callback(parent::getDeclaringClass()->getName(), $this->getName());
	}



	public function __toString()
	{
		return 'Method ' . parent::getDeclaringClass()->getName() . '::' . $this->getName() . '()';
	}



	/********************* Reflection layer ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public function getDeclaringClass()
	{
		return new ClassReflection(parent::getDeclaringClass()->getName());
	}



	/**
	 * @return Nette\Reflection\MethodReflection
	 */
	public function getPrototype()
	{
		$prototype = parent::getPrototype();
		return new MethodReflection($prototype->getDeclaringClass()->getName(), $prototype->getName());
	}



	/**
	 * @return Nette\Reflection\ExtensionReflection
	 */
	public function getExtension()
	{
		return ($name = $this->getExtensionName()) ? new ExtensionReflection($name) : NULL;
	}



	public function getParameters()
	{
		$me = array(parent::getDeclaringClass()->getName(), $this->getName());
		foreach ($res = parent::getParameters() as $key => $val) {
			$res[$key] = new ParameterReflection($me, $val->getName());
		}
		return $res;
	}



	/********************* Nette\Annotations support ****************d*g**/



	/**
	 * Has method specified annotation?
	 * @param  string
	 * @return bool
	 */
	public function hasAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return !empty($res[$name]);
	}



	/**
	 * Returns an annotation value.
	 * @param  string
	 * @return IAnnotation
	 */
	public function getAnnotation($name)
	{
		$res = AnnotationsParser::getAll($this);
		return isset($res[$name]) ? end($res[$name]) : NULL;
	}



	/**
	 * Returns all annotations.
	 * @return array
	 */
	public function getAnnotations()
	{
		return AnnotationsParser::getAll($this);
	}


	/**
	 * Returns allowed types for parameters.
	 * @return array
	 */
	public function getAllowedParametersTypes()
	{
		$annotations = $this->getAnnotations();
		$types = array();
		if (!isset($annotations['param'])) return $types;
		$i = 0;
		foreach ($annotations['param'] as $value) {
			if (($pos = strpos($value, ' ')) !== FALSE) $value = substr($value, 0, $pos);
			$tmp = explode('|', strtolower($value));
			foreach ($tmp as $k => $v) {
				switch ($v) {
					case 'mixed':
						$tmp = NULL;
						break 2;
					case 'int':
						$tmp[$k] = 'integer';
						break;
					case 'bool':
						$tmp[$k] = 'boolean';
						break;
					case 'float':
						$tmp[$k] = 'double';
						break;
					/*case NULL:
						$tmp[$k] = 'null';
						break;
					case TRUE:
						$tmp[$k] = 'true';
						break;
					case FALSE:
						$tmp[$k] = 'false';
						break;*/

				}
			}
			$types[$i++] = $tmp;
		}
		return $types;
	}



	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassReflection
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassReflection(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function __call($name, $args)
	{
		return ObjectMixin::call($this, $name, $args);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		throw new \MemberAccessException("Cannot unset the property {$this->reflection->name}::\$$name.");
	}

}
