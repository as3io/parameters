<?php

namespace As3\Parameters;

class Definitions
{
    /**
     * @var array
     */
    private $fields = [];

    /**
     * Adds a field definition.
     *
     * @param   string  $key
     * @param   string  $type
     * @param   mixed   $default
     * @param   bool    $required
     * @return  self
     */
    public function add($key, $type, $default = null, $required = false)
    {
        $required = (Boolean) $required;
        $this->fields[$key] = [
            'type'      => $type,
            'required'  => $required,
            'default'   => $default,
        ];
        return $this;
    }

    /**
     * @return  array
     */
    public function all()
    {
        return $this->fields;
    }

    /**
     * @param   string  $key
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertFor($key, $value)
    {
        if (!isset($this->fields[$key])) {
            return $value;
        }
        return $this->convertValue($this->fields[$key]['type'], $value);
    }

    /**
     * @param   string  $type
     * @param   mixed   $value
     * @return  mixed
     */
    public function convertValue($type, $value)
    {
        if (null === $value) {
            return;
        }

        if (0 === stripos($type, 'array')) {
            return $this->convertArrayValue($type, $value);
        }

        switch ($type) {
            case 'identifier':
                if (is_numeric($value)) {
                    return (Integer) $value;
                }
                return (String) $value;
            case 'object':
                return (Array) $value;
            case 'string':
                return (String) $value;
            case 'integer':
                return (Integer) $value;
            case 'date':
                if ($value instanceof \DateTime) {
                    return $value;
                }
                $date = new \DateTime();
                $value = is_numeric($value) ? (Integer) $value : strtotime($value);
                $date->setTimestamp($value);
                return $date;
            case 'boolean':
                if ('true' === $value) {
                    return true;
                }
                if ('false' === $value) {
                    return false;
                }
                return (Boolean) $value;
            default:
                return $value;
        }
    }

    /**
     * @param   array   $fieldSet
     * @return  array
     */
    public function format(array $fieldSet)
    {
        $formatted = [];
        foreach ($this->all() as $key => $field) {
            $value = isset($fieldSet[$key]) ? $fieldSet[$key] : $field['default'];
            $formatted[$key] = $this->convertValue($field['type'], $value);
        }
        return $formatted;
    }

    /**
     * @param   string  $key
     * @return  mixed
     * @throws  \InvalidArgumentException
     */
    public function get($key)
    {
        if (!isset($this->fields[$key])) {
            throw new \InvalidArgumentException(sprintf('Field key "%s" is not defined.', $key));
        }
        return $this->fields[$key];
    }

    /**
     * @param   string  $key
     * @return  bool
     */
    public function has($key)
    {
        return isset($this->fields[$key]);
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
        $field = $this->get($key);
        return $value === $field['default'];
    }

    /**
     * @return  array
     */
    public function keys()
    {
        return array_keys($this->fields);
    }

    /**
     * Removes a field key from the definition.
     *
     * @param   string  $key
     * @return  self
     */
    public function remove($key)
    {
        if (isset($this->fields[$key])) {
            unset($this->fields[$key]);
        }
        return $this;
    }

    /**
     * @param   array   $fieldSet
     * @return  bool
     */
    public function valid(array $fieldSet)
    {
        foreach ($this->all() as $key => $field) {
            if (true === $field['required'] && !isset($fieldSet[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param   string  $type
     * @param   mixed   $value
     * @return  array
     */
    private function convertArrayValue($type, $value)
    {
        $value = (Array) $value;
        // array:string,boolean
        if ('array' === $type) {
            return $value;
        }
        $parts = explode(':', $type);
        $keyValues = explode(',', $parts[1]);

        $formatted = [];
        foreach ($value as $k => $v) {
            if (1 === count($keyValues)) {
                // Format value only.
                $formatted[$k] = $this->convertValue($keyValues[0], $v);
            } else {
                // Format key and value.
                $formatted[$this->convertValue($keyValues[0], $k)] = $this->convertValue($keyValues[1], $v);
            }
        }
        return $formatted;
    }
}
