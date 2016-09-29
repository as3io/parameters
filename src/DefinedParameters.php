<?php

namespace As3\Parameters;

class DefinedParameters extends Parameters
{
    /**
     * @var Definitions
     */
    private $definitions;

    /**
     * Constructor.
     *
     * @param   Definitions $definitions
     * @param   array       $parameters
     * @param   string      $sep
     */
    public function __construct(Definitions $definitions, array $parameters = [], $sep = '.')
    {
        $this->definitions = $definitions;
        parent::__construct($parameters, $sep);
    }

    /**
     * Determines if the parameter exists for the provided key.
     *
     * @param   string  $key
     * @return  bool
     */
    public function hasParameterFor($key)
    {
        return $this->definitions->has($key);
    }

    /**
     * Determines if a value is the same as the default for the provided field key.
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  bool
     */
    public function isDefaultValue($key, $value)
    {
        if (false === $this->definitions->has($key)) {
            return true;
        }
        return $this->definitions->isDefaultValue($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function mergeFromArray(array $parameters)
    {
        $parameters = $this->definitions->format($parameters);
        return parent::mergeFromArray($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function replace(array $parameters)
    {
        $parameters = $this->definitions->format($parameters);
        return parent::replace($parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function set($path, $value)
    {
        $keys = $this->explode($path);
        if (count($keys) > 1) {
            throw new \BadMethodCallException('Setting values via a path for a defined parameter instance is not yet implemented.');
        }
        if (false === $this->definitions->has($keys[0])) {
            return $this;
        }
        $value = $this->definitions->convertFor($keys[0], $value);
        return parent::set($keys[0], $value);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->definitions->valid($this->all());
    }
}
