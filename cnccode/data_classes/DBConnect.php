<?php
/**
 * Created by PhpStorm.
 * User: fizda
 * Date: 20/09/2017
 * Time: 8:48
 */

class DBConnect
{
    /**
     * Singleton class
     */
    private static $db = null;

    /**
     * PDO instance
     */
    private static $pdo;

    final private function __construct()
    {
        try {
            //create new PDO connection
            self::getDB();
        } catch (PDOException $e) {
            //Exception handling
        }
    }

    /**
     * Creates a new PDO connection using
     * the mysql constants
     */
    public function getDB()
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                'mysql:dbname=' . DB_NAME . ";" . "host=" . DB_HOST . ";",
                DB_USER,
                DB_PASSWORD,
                [PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '+00:00'"]
            );
            //exception enabled
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        return self::$pdo;
    }

    public static function execute($query, $params = null)
    {
        if ($params) {
            $stmt = self::instance()->getDB()->prepare($query);
            if ($params) foreach ($params as $key => $value) $stmt->bindParam($key, $params[$key]);
            return $stmt->execute();
        } else
            return self::instance()->getDB()->prepare($query)->execute();
    }


    /**
     * Returns the singleton instance
     * @return DBConnect|null
     */
    public static function instance()
    {
        if (self::$db === null) {
            self::$db = new self();
        }
        return self::$db;
    }

    final public function __destruct()
    {
        self::$pdo = null;
    }

    /**
     * Prevent object to be cloned
     */
    final protected function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function fetchOne($query, $params = [])
    {
        $stmt = self::instance()->getDB()->prepare($query);
        if ($params) foreach ($params as $key => $value) $stmt->bindParam($key, $params[$key]);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function fetchAll($query, $params = [])
    {
        $stmt = self::instance()->getDB()->prepare($query);
        foreach ($params as $key => $value) {
            if (($params[$key] != null || $params[$key] == '0') && is_numeric($params[$key])) {
                $params[$key] = (int)$params[$key];
                $stmt->bindParam($key, $params[$key], PDO::PARAM_INT);
            } else
                $stmt->bindParam($key, $params[$key]);

        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function lastInsertedId()
    {
        return DBConnect::instance()->getDB()->lastInsertId();
    }
}