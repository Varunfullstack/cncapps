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
            self::getDB();
        } catch (Exception $e) {
            //Exception handling
        }
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

    /**
     * Creates a new PDO connection using
     * the mysql constants
     */
    public function getDB()
    {
        if (self::$pdo === null) {
            self::$pdo = new PDO(
                'mysql:dbname=' . DB . ";" .
                "host=" . HOST_NAME . ";charset=utf8mb4",
                USER,
                PWD

            );

            //exception enabled
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        return self::$pdo;
    }

    /**
     * Prevent object to be cloned
     */
    final protected function __clone()
    {
        // TODO: Implement __clone() method.
    }

    final public function __destruct()
    {
        self::$pdo = null;
    }
}