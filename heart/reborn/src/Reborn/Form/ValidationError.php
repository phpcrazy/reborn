<?php

namespace Reborn\Form;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;

/**
 * ValidationError Class
 *
 * @package Reborn\Form
 * @author Myanmar Links Professional Web Development
 **/
class ValidationError implements ArrayAccess, IteratorAggregate
{

	/**
	 * Error messages array
	 *
	 * @var array
	 **/
	protected $errors = array();

	/**
	 * Construct method for ValidationError
	 *
	 * @param array $errors Error messages (Optional)
	 * @return ValidationError
	 **/
	public function __construct($errors = null)
	{
		if (!is_null($errors)) {
			$this->errors = (array) $errors;
		}

		return $this;
	}

	/**
	 * Set Errors for ValidationError
	 *
	 * @param array $errros Errors messages
	 * @return ValidationError
	 **/
	public function setErrors(array $errors)
	{
		$this->errors = $errors;

		return $this;
	}

	public function offsetSet($key, $value)
	{
        $this->errors[$key] = $value;
    }

    public function offsetExists($key) {
        return isset($this->errors[$key]);
    }

    public function offsetUnset($key) {
        unset($this->errors[$key]);
    }

    public function offsetGet($key) {
        return isset($this->errors[$key]) ? $this->errors[$key] : null;
    }

    /**
     * Get an iterator for the data.
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->errors);
    }

    /**
     * Get error message as array
     *
     * @return array
     */
    public function toArray()
    {
    	return $this->errors;
    }

    /**
     * Get error message as json
     *
     * @param integer $options
     * @return string
     */
    public function toJson($options = 0)
    {
    	return json_encode($this->errors, $options);
    }

    /**
     * Magic setter method
     *
     * @param string $key
     * @param mixed $value
     * @return void
     **/
    public function __set($key, $value)
    {
        $this->errors[$key] = $value;
    }

    /**
     * Magic getter method
     *
     * @param string $key
     * @return mixed
     **/
    public function __get($key)
    {
        return isset($this->errors[$key]) ? $this->errors[$key] : null;
    }

    /**
     * Magic toString method
     *
     * @return string
     **/
    public function __toString()
    {
    	$errors = implode("\n", $this->errors);

    	return $errors;
    }

} // END class ValidationError implements ArrayAccess
