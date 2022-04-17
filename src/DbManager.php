<?php

namespace xooooooox\database;

class DbManager {

    /**
     * @var array
     */
    protected static $instance = [];

    /**
     * @param \PDO $instance
     * @param string $name
     */
    public static function put($instance, $name = 'default'){
        if(empty($name) || empty($instance)){
            return;
        }
        static::$instance[$name] = $instance;
    }

    /**
     * @return \PDO|null
     */
    public static function get($name = 'default'){
        if(isset(static::$instance[$name])){
            return static::$instance[$name];
        }
        return null;
    }

}