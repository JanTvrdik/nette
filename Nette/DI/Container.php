<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 *
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nette\DI;

use Nette;



/**
 * The dependency injection container default implementation.
 *
 * @author     David Grudl
 */
class Container extends Nette\Object
{
	const TAGS = 'tags';

	/** @var array  user parameters */
	/*private*/public $parameters = array();

	/** @var array */
	public $classes = array();

	/** @var array  storage for shared objects */
	private $registry = array();

	/** @var array */
	public $meta = array();

	/** @var array circular reference detector */
	private $creating;



	public function __construct(array $params = array())
	{
		$this->parameters = $params + $this->parameters;
	}



	/**
	 * @return array
	 */
	public function getParameters()
	{
		return $this->parameters;
	}



	/**
	 * Adds the service to the container.
	 * @param  string
	 * @param  object
	 * @param  array   service meta information
	 * @return Container  provides a fluent interface
	 */
	public function addService($name, $service, array $meta = NULL)
	{
		if (!is_string($name) || !$name) {
			throw new Nette\InvalidArgumentException('Service name must be a non-empty string, ' . gettype($name) . ' given.');

		} elseif (isset($this->registry[$name])) {
			throw new Nette\InvalidStateException("Service '$name' already exists.");

		} elseif (is_string($service) || $service instanceof \Closure || $service instanceof Nette\Callback) {
			trigger_error('Passing factories to ' . __METHOD__ . '() is deprecated; pass service object itself.', E_USER_DEPRECATED);
			$service = is_string($service) && !preg_match('#\x00|:#', $service) ? new $service : call_user_func($service, $this);
		}

		if (!is_object($service)) {
			throw new Nette\InvalidArgumentException('Service must be a object, ' . gettype($service) . ' given.');
		}

		$this->registry[$name] = $service;
		$this->meta[$name] = $meta;
		return $this;
	}



	/**
	 * Removes the service from the container.
	 * @param  string
	 * @return void
	 */
	public function removeService($name)
	{
		unset($this->registry[$name], $this->meta[$name]);
	}



	/**
	 * Gets the service object by name.
	 * @param  string
	 * @return object
	 */
	public function getService($name)
	{
		if (isset($this->registry[$name])) {
			return $this->registry[$name];

		} elseif (isset($this->creating[$name])) {
			throw new Nette\InvalidStateException('Circular reference detected for services: '
				. implode(', ', array_keys($this->creating)) . '.');

		} elseif (method_exists($this, $method = Container::getMethodName($name)) && $this->getReflection()->getMethod($method)->getName() === $method) {
			$this->creating[$name] = TRUE;
			try {
				$service = $this->$method();
			} catch (\Exception $e) {
				unset($this->creating[$name]);
				throw $e;
			}
			unset($this->creating[$name]);
			if (!is_object($service)) {
				throw new Nette\UnexpectedValueException("Unable to create service '$name', value returned by method $method() is not object.");
			}
			return $this->registry[$name] = $service;

		} else {
			throw new MissingServiceException("Service '$name' not found.");
		}
	}



	/**
	 * Does the service exist?
	 * @param  string service name
	 * @return bool
	 */
	public function hasService($name)
	{
		return isset($this->registry[$name])
			|| method_exists($this, $method = Container::getMethodName($name)) && $this->getReflection()->getMethod($method)->getName() === $method;
	}



	/**
	 * Is the service created?
	 * @param  string service name
	 * @return bool
	 */
	public function isCreated($name)
	{
		if (!$this->hasService($name)) {
			throw new MissingServiceException("Service '$name' not found.");
		}
		return isset($this->registry[$name]);
	}



	/**
	 * Resolves service by type.
	 * @param  string  class or interface
	 * @param  bool    throw exception if service doesn't exist?
	 * @return object  service or NULL
	 * @throws MissingServiceException
	 */
	public function getByType($class, $need = TRUE)
	{
		$lower = ltrim(strtolower($class), '\\');
		if (!isset($this->classes[$lower])) {
			if ($need) {
				throw new MissingServiceException("Service of type $class not found.");
			}
		} elseif ($this->classes[$lower] === FALSE) {
			throw new MissingServiceException("Multiple services of type $class found.");
		} else {
			return $this->getService($this->classes[$lower]);
		}
	}



	/**
	 * Gets the service names of the specified tag.
	 * @param  string
	 * @return array of [service name => tag attributes]
	 */
	public function findByTag($tag)
	{
		$found = array();
		foreach ($this->meta as $name => $meta) {
			if (isset($meta[self::TAGS][$tag])) {
				$found[$name] = $meta[self::TAGS][$tag];
			}
		}
		return $found;
	}



	/********************* autowiring ****************d*g**/



	/**
	 * Creates new instance using autowiring.
	 * @param  string  class
	 * @param  array   arguments
	 * @return object
	 * @throws Nette\InvalidArgumentException
	 */
	public function createInstance($class, array $args = array())
	{
		$rc = Nette\Reflection\ClassType::from($class);
		if (!$rc->isInstantiable()) {
			throw new ServiceCreationException("Class $class is not instantiable.");

		} elseif ($constructor = $rc->getConstructor()) {
			return $rc->newInstanceArgs(Helpers::autowireArguments($constructor, $args, $this));

		} elseif ($args) {
			throw new ServiceCreationException("Unable to pass arguments, class $class has no constructor.");
		}
		return new $class;
	}



	/**
	 * Calls all methods starting with with "inject" using autowiring.
	 * @param  object
	 * @return void
	 */
	public function callInjects($service)
	{
		if (!is_object($service)) {
			throw new Nette\InvalidArgumentException('Service must be object, ' . gettype($service) . ' given.');
		}

		foreach (array_reverse(get_class_methods($service)) as $method) {
			if (substr($method, 0, 6) === 'inject') {
				$this->callMethod(array($service, $method));
			}
		}

		foreach (Helpers::getInjectProperties(Nette\Reflection\ClassType::from($service)) as $property => $type) {
			$service->$property = $this->getByType($type);
		}
	}



	/**
	 * Calls method using autowiring.
	 * @param  mixed   class, object, function, callable
	 * @param  array   arguments
	 * @return mixed
	 */
	public function callMethod($function, array $args = array())
	{
		$callback = new Nette\Callback($function);
		return $callback->invokeArgs(Helpers::autowireArguments($callback->toReflection(), $args, $this));
	}



	/********************* shortcuts ****************d*g**/



	/**
	 * Expands %placeholders%.
	 * @param  mixed
	 * @return mixed
	 */
	public function expand($s)
	{
		return Helpers::expand($s, $this->parameters);
	}



	/** @deprecated */
	public function &__get($name)
	{
		if (empty($this->parameters['nette']['accessors'])) {
			trigger_error(__METHOD__ . '() is deprecated; use getService() instead.', E_USER_DEPRECATED);
		}
		$tmp = $this->getService($name);
		return $tmp;
	}



	/** @deprecated */
	public function __set($name, $service)
	{
		if (empty($this->parameters['nette']['accessors'])) {
			trigger_error(__METHOD__ . '() is deprecated; use addService() instead.', E_USER_DEPRECATED);
		}
		$this->addService($name, $service);
	}



	/** @deprecated */
	public function __isset($name)
	{
		if (empty($this->parameters['nette']['accessors'])) {
			trigger_error(__METHOD__ . '() is deprecated; use hasService() instead.', E_USER_DEPRECATED);
		}
		return $this->hasService($name);
	}



	/** @deprecated */
	public function __unset($name)
	{
		if (empty($this->parameters['nette']['accessors'])) {
			trigger_error(__METHOD__ . '() is deprecated; use removeService() instead.', E_USER_DEPRECATED);
		}
		$this->removeService($name);
	}



	public static function getMethodName($name, $isService = TRUE)
	{
		$uname = ucfirst($name);
		return ($isService ? 'createService' : 'create') . ((string) $name === $uname ? '__' : '') . str_replace('.', '__', $uname);
	}

}
