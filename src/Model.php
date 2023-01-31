<?php

namespace Digitalize\SDK;

use Digitalize\SDK\Exceptions\TypeException;

/**
 * Base class for all API models
 */
class Model
{
    /**
     * Defines the model fields types.
     * 
     * Can be simple type (boolean, bool, integer, int, float, double, string, date, datetime) or complex types.
     * 
     * Form : 
     * - For simple type : 
     *   
     *   $_types = ['id' => 'int'];
     *
     * - For complex type :
     *   
     *   $_types = ['posts' => ['type' => Post::class, 'multiple' => true]]
     *
     * @var array
     */
    protected $_types = [];

    /**
     * Instanciates a new instance of the model, and hydrates it with provided data.
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        foreach ($data as $k => $v) {
            if (!property_exists($this, $k))
                continue;
            $this->set($k, $v);
        }
    }

    /**
     * Sets a new value and formats it
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value)
    {
        if (isset($this->_types[$key])) {
            $type = $this->_types[$key];
            if (is_string($type))
                $type = ['type' => $type, 'multiple' => false];
            if (!isset($type['multiple']) || $type['multiple'] === false) {
                $this->$key = $this->_castValue($type, $value);
            } else {
                $this->$key = array_map(function ($val) use ($type) {
                    return $this->_castValue($type, $val);
                }, $value);
            }
        } else {
            $this->$key = $value;
        }
        return $this;
    }

    /**
     * Casts a value from the API into the right format.
     *
     * @param array $type
     * @param mixed $v
     * @return mixed
     */
    protected function _castValue($type, $v)
    {
        switch ($type['type']) {
            case 'boolean':
            case 'bool':
            case 'integer':
            case 'int':
            case 'float':
            case 'double':
            case 'string':
                settype($v, $type['type']);
                break;
            case 'date':
                /**
                 * For date time, we handle multiple formats : 
                 * - String format : we directly parse it with strtotime()
                 * - Array format : we handle timezone information and parse the date
                 */
                if (is_array($v) && isset($v['date'])) {
                    if (isset($v['timezone'])) {
                        $originalTZ = date_default_timezone_get();
                        date_default_timezone_set($v['timezone']);
                    }
                    $v = date('Y-m-d', strtotime($v['date']));
                    if (isset($v['timezone'])) {
                        date_default_timezone_set($originalTZ);
                    }
                } elseif (is_string($v))
                    $v = date('Y-m-d', strtotime($v));
                break;
            case 'datetime':
                /**
                 * For date time, we handle multiple formats : 
                 * - String format : we directly parse it with strtotime()
                 * - Array format : we handle timezone information and parse the date
                 */
                if (is_array($v) && isset($v['date'])) {
                    if (isset($v['timezone'])) {
                        $originalTZ = date_default_timezone_get();
                        date_default_timezone_set($v['timezone']);
                    }
                    $v = date('Y-m-d H:i:s', strtotime($v['date']));
                    if (isset($v['timezone'])) {
                        date_default_timezone_set($originalTZ);
                    }
                } elseif (is_string($v))
                    $v = date('Y-m-d H:i:s', strtotime($v));
                break;
            default:
                /**
                 * If the type is an existant class, we instanciate it
                 */
                if (class_exists($type['type'])) {
                    $v = new $type['type']($v);
                } else {
                    throw new TypeException("Type '{$type['type']}' is not defined.");
                }
                break;
        }

        return $v;
    }

    /**
     * Outputs model data in var_dump
     *
     * @return array
     */
    public function __debugInfo()
    {
        return $this->export();
    }

    /**
     * Returns model data
     *
     * @return array
     */
    public function export()
    {
        if (method_exists($this, '__beforeExport'))
            $this->__beforeExport();
        $ret = [];
        foreach ($this as $key => $field) {
            if ($key === '_types') {
                continue;
            }
            $type = isset($this->_types[$key]) ? $this->_types[$key] : false;
            if (is_string($type))
                $type = ['type' => $type, 'multiple' => false];
            switch ($type['type']) {
                case 'boolean':
                case 'bool':
                case 'integer':
                case 'int':
                case 'float':
                case 'double':
                case 'string':
                case 'date':
                case 'datetime':
                    $ret[$key] = $field;
                    break;
                default:
                    if (!isset($type['multiple']) || !$type['multiple']) {
                        if ($field instanceof self) {
                            $ret[$key] = $field->export();
                        } else {
                            $ret[$key] = $field;
                        }
                    } else {
                        $ret[$key] = array_map(function ($model) {
                            if ($model instanceof self) {
                                return $model->export();
                            }
                            return $model;
                        }, $field);
                    }
                    break;
            }
        }
        if (method_exists($this, '__afterExport'))
            $ret = $this->__afterExport($ret);
        return $ret;
    }
}
