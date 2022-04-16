<?php

namespace xooooooox\database;

use \PDO;
use \PDOException;
use \PDOStatement;

/**
 * Class Db
 * @package xooooooox\database
 * @method static \PDO pdo()
 * @method static bool begin()
 * @method static bool commit()
 * @method static bool rollback()
 * @method static array query($sql = '', $params = [])
 * @method static array first($sql = '', $params = [])
 * @method static int execute($sql = '', $params = [])
 * @method static string transaction(callable $transaction, $attempts = 1)
 */
class Db {

    /**
     * @var PDO
     */
    protected $_pdo;

    /**
     * @param string $dsn
     * @param string $user
     * @param string $pass
     * @param array $options
     */
    public function __construct($dsn = '', $user = '', $pass = '', $options = []) {
        if ($options === []){
            $options = [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
        }
        $this->_pdo = new PDO($dsn, $user, $pass, $options);
    }

    /**
     * pdo
     * return PDO
     */
    public function pdo() {
        return $this->_pdo;
    }

    /**
     * begin
     * return bool
     */
    public function begin(){
        return $this->_pdo->beginTransaction();
    }

    /**
     * commit
     * return bool
     */
    public function commit(){
        return $this->_pdo->commit();
    }

    /**
     * rollback
     * return bool
     */
    public function rollback(){
        return $this->_pdo->rollBack();
    }

    /**
     * execute query a sql
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function query($sql = '', $params = []) {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * execute query one row sql
     * @param string $sql
     * @param array $params
     * @return array
     */
    public function first($sql = '', $params = []) {
        $result = $this->query($sql, $params);
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
    public function execute($sql = '', $params = []) {
        $stmt = $this->_pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * execute a transaction, return error message
     * @param callable $transaction
     * @param int $attempts
     * @return string
     */
    public function transaction(callable $transaction, $attempts = 1) {
        $err = '';
        if ($attempts <= 0) {
            $err = 'the execution was unsuccessful, the number of times was exhausted.';
            return $err;
        }
        try {
            $attempts--;
            if(!$this->begin()){
                throw new PDOException('transaction start failed.');
            }
            $transaction($this);
            if(!$this->commit()){
                throw new PDOException('transaction commit failed.');
            }
            $err = '';
        } catch(PDOException $e) {
            $this->rollback();
            $err = $e->getMessage();
            if ($attempts > 0) {
                return $this->transaction($transaction, $attempts);
            }
            return $err;
        }
        return $err;
    }

    /**
     * __callStatic
     */
    public static function __callStatic($name, $arguments) {
        if(method_exists(static::class, $name)){
            call_user_func([new static(), $name], $arguments);
        }
    }

}
