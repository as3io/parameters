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
     * @var string
     */
    private $sep;

    /**
     * Constructor.
     *
     * @param   array   $parameters An array of parameters.
     * @param   string  $sep        The path traversal separator.
     */
    public function __construct(array $parameters = [], $sep = '.')
    {
        $this->replace($parameters);
        $this->sep = $sep;
    }

    /**
     * Static factory method.
     *
     * @param   array   $parameters An array of parameters.
     * @param   string  $sep        The path traversal separator.
     * @return  self
     */
    public static function create(array $parameters = array(), $sep = '.')
    {
        return new static($parameters, $sep);
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
     * Determines if a parameter exists, regardless of null value.
     *
     * @param  string   $path
     * @return bool
     */
    public function exists($path)
    {
        $keys       = $this->explode($path);
        $parameters = $this->parameters;

        foreach ($keys as $key) {
            if (!is_array($parameters)) {
                return false;
            }
            if (array_key_exists($key, $parameters)) {
                $parameters = $parameters[$key];
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets a parameter value.
     *
     * @param   string  $path       The parameter path, such as 'foo' or 'foo.bar'
     * @param   mixed   $default    The default value to return if the key isn't found
     * @return  mixed
     */
    public function get($path, $default = null)
    {
        $keys       = $this->explode($path);
        $parameters = $this->parameters;

        foreach ($keys as $key) {
            if (isset($parameters[$key])) {
                $parameters = $parameters[$key];
            } else {
                return $default;
            }
        }
        return $parameters;
    }

    /**
     * Gets a value based on a path, and returns an empty array if not found.
     *
     * @param   string  $path   The parameter path, such as 'foo' or 'foo.bar'
     * @return  array
     */
    public function getAsArray($path)
    {
        return $this->get($path, []);
    }

    /**
     * Gets a value based on a path, and returns it as a Parameters instance.
     *
     * @param   string  $path   The parameter path, such as 'foo' or 'foo.bar'
     * @return  self
     */
    public function getAsInstance($path)
    {
        if (empty($path)) {
            return static::create([], $this->separator);
        }
        $value = $this->getAsArray($path);
        if (!is_array($value)) {
            throw new \InvalidArgumentException('You cannot return a Parameter instance on a non-array value.');
        }
        return static::create($this->getAsArray($path), $this->separator);
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
     * @param   string  $path
     * @return  bool
     */
    public function has($path)
    {
        return null !== $this->get($path, null);
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
     * Removes/unsets a value at the provided path.
     *
     * @param   string  $key
     * @return  self
     */
    public function remove($path)
    {
        $keys       = $this->explode($path);
        $parameters = &$this->parameters;

        while (count($keys) > 0) {
            if (count($keys) === 1) {
                if (is_array($parameters)) {
                    unset($parameters[array_shift($keys)]);
                } else {
                    return $this;
                }
            } else {
                $key = array_shift($keys);

                if (!isset($parameters[$key])) {
                    return $this;
                }
                $parameters = &$parameters[$key];
            }
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
        $this->parameters = $this->castObjectsAsArrays($parameters);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            $this->parameters,
            $this->sep,
        ]);
    }

    /**
     * Sets a value to a key path.
     *
     * @param   string  $path
     * @param   mixed   $value
     * @return  self
     */
    public function set($path, $value)
    {
        $keys       = $this->explode($path);
        $parameters = &$this->parameters;

        while (count($keys) > 0) {
            if (count($keys) === 1) {
                if (!is_array($parameters)) {
                    $parameters = [];
                }
                $parameters[array_shift($keys)] = $value;
            } else {
                $key = array_shift($keys);
                if (!isset($parameters[$key])) {
                    $parameters[$key] = [];
                }
                $parameters = &$parameters[$key];
            }
        }
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
        list(
            $this->parameters,
            $this->sep
        ) = unserialize($serialized);
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

    /**
     * Explodes a path based on the internal separator.
     *
     * @param   string|array    $path
     * @return  array
     */
    protected function explode($path)
    {
        return is_array($path) ? $path : explode($this->sep, $path);
    }

    /**
     * Ensures any object values are cast as arrays. Is recursive.
     *
     * @param   array   $parameters
     * @return  array
     */
    private function castObjectsAsArrays(array $parameters)
    {
        foreach ($parameters as $key => $value) {
            if (is_object($value)) {
                $value = (array) $value;
                $parameters[$key] = $value;
            }
            if (is_array($value)) {
                $parameters[$key] = $this->castObjectsAsArrays($value);
            }
        }
        return $parameters;
    }
}
