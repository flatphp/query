<?php namespace Flatphp\Query;


abstract class QueryResolver
{
    protected static $namespace = '';

    public static function __callStatic($method, $params)
    {
        $method = static::$namespace .'\\'. ucfirst($method) .'Query';
        return new $method(...$params);
    }
}