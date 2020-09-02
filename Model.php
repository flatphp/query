<?php namespace Flatphp\Query;


abstract class Model implements \ArrayAccess
{
    protected $data = [];

    public function __construct(array $data = [])
    {
        $this->fill($data);
    }

    public function fill(array $data)
    {
        foreach ($data as $k => $v) {
            $this->set($k, $v);
        }
    }

    protected function set($key, $value)
    {
        $method = 'set'. str_replace('_', '', ucwords($key, '_'));
        if (method_exists($this, $method)) {
            $this->$method($value);
        } else {
            $this->setAttr($key, $value);
        }
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->getAttr($key);
    }

    public function toArray()
    {
        return $this->data;
    }

    public function toJson()
    {
        return json_encode($this->data);
    }

    protected function setAttr($key, $value)
    {
        $this->data[$key] = $value;
    }

    protected function getAttr($key, $default = null)
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }


    /**
     * Determine if the given attribute exists.
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttr($offset));
    }

    /**
     * Get the value for a given offset.
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttr($offset);
    }

    /**
     * Set the value for a given offset.
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }
}