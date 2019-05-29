<?php

class CNCMysqli
{
    /**
     * @var mysqli $mysqliInstance
     */
    private static $mysqliInstance;

    /**
     * @var CNCMysqli $_instance
     */
    private static $_instance;

    final private function __construct()
    {
        try {
            self::getDB();
        } catch (Exception $exception) {
            die('Database error');
        }
    }

    /**
     * @return CNCMysqli
     */
    public static function instance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * @return mysqli
     */
    public function getDB()
    {
        if (self::$mysqliInstance === null) {
            self::$mysqliInstance = mysqli_init();

            if (!self::$mysqliInstance->options(MYSQLI_INIT_COMMAND, 'SET AUTOCOMMIT = 1')) {
                die('Setting MYSQLI_INIT_COMMAND failed');
            }

            if (!self::$mysqliInstance->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5)) {
                die('Setting MYSQLI_OPT_CONNECT_TIMEOUT failed');
            }

            if (!self::$mysqliInstance->real_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME)) {
                die('Connect Error (' . self::$mysqliInstance->connect_errno . ') '
                    . self::$mysqliInstance->connect_error);
            }
        }
        return self::$mysqliInstance;
    }

    final public function __destruct()
    {
        self::$mysqliInstance->close();
    }
}
