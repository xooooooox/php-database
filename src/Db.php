<?php

namespace xooooooox\database;

use \PDO;
use \PDOException;

/**
 * Class Db
 * @package xooooooox\database
 */
class Db {

    /**
     * @var \PDO
     */
    protected static $_instance = null;

    /**
     * instance a connect
     */
    public static function instance($dsn = '', $user = '', $pass = '', $options = [], $name = 'default'){
        if ($options === []){
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
        }
        $pdo = new PDO($dsn, $user, $pass, $options);
        DbManager::put($pdo,$name);
        if($name === 'default' || static::$_instance === null){
            static::$_instance = $pdo;
        }
    }

    /**
     * @return \PDO|null
     */
    public static function getInstance($name = 'default'){
        return DbManager::get($name);
    }

    /**
     * @param \PDO $instance
     * @param string $name
     */
    public static function putInstance($instance, $name = 'default'){
        return DbManager::put($instance, $name);
    }

    /**
     * begin
     * return bool
     */
    public static function begin(){
        return static::$_instance->beginTransaction();
    }

    /**
     * commit
     * return bool
     */
    public static function commit(){
        return static::$_instance->commit();
    }

    /**
     * rollback
     * return bool
     */
    public static function rollback(){
        return static::$_instance->rollBack();
    }

    /**
     * execute query a sql
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function query($sql = '', $params = []) {
        $stmt = static::$_instance->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * execute query one row sql
     * @param string $sql
     * @param array $params
     * @return array
     */
    public static function first($sql = '', $params = []) {
        $result = static::query($sql, $params);
        if (isset($result[0])){
            return $result[0];
        }
        return [];
    }

    /**
     * execute a sql, return the number of rows affected
     * @param string $sql
     * @param array $params
     * @return int
     */
    public static function execute($sql = '', $params = []) {
        $stmt = static::$_instance->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * execute a transaction, return error message
     * @param callable $transaction
     * @param int $attempts
     * @return string
     */
    public static function transaction(callable $transaction, $attempts = 1) {
        $err = '';
        if ($attempts <= 0) {
            $err = 'the execution was unsuccessful, the number of times was exhausted.';
            return $err;
        }
        try {
            $attempts--;
            if(!static::begin()){
                throw new PDOException('transaction start failed.');
            }
            $transaction(static::$_instance);
            if(!static::commit()){
                throw new PDOException('transaction commit failed.');
            }
            $err = '';
        } catch(PDOException $e) {
            static::rollback();
            $err = $e->getMessage();
            if ($attempts > 0) {
                return static::transaction($transaction, $attempts);
            }
            return $err;
        }
        return $err;
    }

}
