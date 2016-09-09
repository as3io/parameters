<?php

namespace As3\Parameters;

use \ArrayIterator;
use \Countable;
use \IteratorAggregate;
use \JsonSerializable;
use \Serializable;

class Parameters implements IteratorAggregate, Countable, Serializable, JsonSerializable
{
    /**
     * Parameter storage.
     *
     * @var array
     */
    private $parameters;

    /**
     * Constructor.
     *
     * @param  array    $parameters     An array of parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->replace($parameters);
    }

    /**
     * Returns the parameters.
     *
     * @return  array
     */
    public function all()
    {
        return $this->parameters;
    }

    /**
     * Determines if the paramters are empty.
     *
     * @return  bool
     */
    public function areEmpty()
    {
        return 0 === count($this);
    }

    /**
     * Clears the current parameters.
     *
     * @param   array   $parameters
     * @return  self
     */
    public function clear()
    {
        return $this->replace([]);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->parameters);
    }

    /**
     * Gets a parameter value.
     *
     * @param   string  $key        The parameter key.
     * @param   mixed   $default    The default value to return if the key isn't found
     * @return  mixed
     */
    public function get($key, $default = null)
    {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->parameters);
    }

    /**
     * Determines if a parameter key exists, and has value (not null).
     *
     * @param   string  $key
     * @return  bool
     */
    public function has($key)
    {
        return null !== $this->get($key, null);
    }

    /**
     * Returns the parameter keys.
     *
     * @return  array
     */
    public function keys()
    {
        return array_keys($this->parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->all();
    }

    /**
     * Merges the parameters with the provided parameters instance
     *
     * @param  Parameters   $parameters
     * @return self
     */
    public function merge(Parameters $parameters)
    {
        return $this->mergeFromArray($parameters->all());
    }

    /**
     * Merges the parameters with the provided key/values.
     *
     * @param  array    $parameters
     * @return self
     */
    public function mergeFromArray(array $parameters)
    {
        $this->parameters = array_replace_recursive($this->parameters, $parameters);
        return $this;
    }

    /**
     * Removes a parameter.
     *
     * @param   string  $key
     * @return  self
     */
    public function remove($key)
    {
        if ($this->has($key)) {
            unset($this->parameters[$key]);
        }
        return $this;
    }

    /**
     * Replaces the current parameters by a new set.
     *
     * @param   array   $parameters]
     * @return  self
     */
    public function replace(array $parameters)
    {
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize($this->parameters);
    }

    /**
     * Sets a value to a key.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  self
     */
    public function set($key, $value)
    {
        $this->parameters[$key] = $value;
        return $this;
    }

    /**
     * Returns the parameters instance as an array.
     *
     * @see     all()
     * @return  array
     */
    public function toArray()
    {
        return $this->all();
    }

    /**
     * JSON encodes this parameters instance.
     *
     * @return  string
     */
    public function toJson()
    {
        return json_encode($this);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $this->parameters = unserialize($serialized);
    }

    /**
     * Determines if the parameters are valid.
     *
     * @return  boolean
     */
    public function valid()
    {
        return true;
    }
}
