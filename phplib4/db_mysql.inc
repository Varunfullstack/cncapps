<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998-2000 NetUSE AG
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id: db_mysql.inc,v 1.11 2002/08/07 19:33:57 layne_weathers Exp $
 *
 */

class DB_Sql
{

    /* public: connection parameters */
    /**
     * @var mysqli
     */
    public static $Link_ID  = null;
    var           $Host     = "";
    var           $Database = "";
    var           $User     = "";

    /* public: configuration parameters */
    var $Password      = "";     ## Set to 1 for automatic mysql_free_result()
    var $Auto_Free     = 0;     ## Set to 1 for debugging messages.
    var $Debug         = 0; ## "yes" (halt with message), "no" (ignore errors quietly), "report" (ignore errror, but spit a warning)
    var $Halt_On_Error = "report";     ## Set to 1 to use persistent database connections
    var $PConnect      = 0;

    /* public: result array and current row number */
    var $Seq_Table = "db_sequence";
    var $Record    = array();

    /* public: current error number and error text */
    var $Row;
    var $Errno = 0;

    /* public: this is an api revision, not a CVS revision. */
    var $Error    = "";
    var $type     = "mysql";
    var $revision = "1.2";
    /**
     * @var boolean|mysqli_result
     */
    var $Query_ID = null;

    var $locked = false;      ## set to true while we have a lock

    /* public: constructor */
    function __construct($query = "")
    {
        $this->query($query);
    }

    function query($Query_String)
    {
        /* No empty queries, please, since PHP4 chokes on them. */
        if ($Query_String == "") /* The empty query string is passed on from the constructor,
             * when calling the class without a query, e.g. in situations
             * like these: '$db = new DB_Sql_Subclass;'
             */ return 0;
        if (!$this->connect()) {
            return 0; /* we already complained in connect() about that. */
        };
        # New query, discard previous result.
        if ($this->Query_ID) {
            $this->free();
        }
        if ($this->Debug) printf(
            "Debug: query = %s<br>\n",
            $Query_String
        );
        $this->Query_ID = mysqli_query(
            self::$Link_ID,
            $Query_String
        );
        $this->Row      = 0;
        $this->Errno    = mysqli_errno(self::$Link_ID);
        $this->Error    = mysqli_error(self::$Link_ID);
        if (!$this->Query_ID) {
//            $this->halt("Invalid SQL: " . $Query_String);
        }
        # Will return nada if it fails. That's fine.
        return $this->Query_ID;
    }

    function beginTransaction()
    {
        $this->link_id()->begin_transaction();
    }

    function commit()
    {
        $this->link_id()->commit();
    }

    function connect($Database = "",
                     $Host = "",
                     $User = "",
                     $Password = ""
    )
    {
        /* Handle defaults */
        if ("" == $Database) $Database = $this->Database;
        if ("" == $Host) $Host = $this->Host;
        if ("" == $User) $User = $this->User;
        if ("" == $Password) $Password = $this->Password;
        /* establish connection, select database */
        if (!isset(self::$Link_ID)) {
            self::$Link_ID = mysqli_connect(
                $Host,
                $User,
                $Password,
                $Database
            );
            if (!self::$Link_ID) {
                $this->halt(
                    "Failed to connect($Host, $User, \$Password) failed." . json_encode(mysqli_connect_error())
                );
                return 0;
            }
            if (!self::$Link_ID->set_charset("utf8mb4")) {
                printf(
                    "Error loading utf8 charset: %s\n",
                    self::$Link_ID->error
                );
                exit();
            }
            if (!@mysqli_select_db(
                self::$Link_ID,
                $Database
            )) {
                $this->halt("cannot use database " . $Database);
                return 0;
            }
        }
        return self::$Link_ID;
    }

    /* public: connection management */
    function halt($msg)
    {
        $this->Error = @mysqli_error(self::$Link_ID);
        $this->Errno = @mysqli_errno(self::$Link_ID);
        if ($this->locked) {
            $this->unlock();
        }
        if ($this->Halt_On_Error == "no") return;
        $this->haltmsg($msg);
        if ($this->Halt_On_Error != "report") die("Session halted.");
    }

    /* public: discard the query result */
    function unlock()
    {

        // set before unlock to avoid potential loop
        $this->locked = false;
        if (!$this->query("unlock tables")) {
            $this->halt("unlock() failed.");
            return false;
        }
        return true;
    }

    function haltmsg($msg)
    {
        printf(
            "</td></tr></table><b>Database error:</b> %s<br>\n",
            $msg
        );
        printf(
            "<b>MySQL Error</b>: %s (%s)<br>\n",
            $this->Errno,
            $this->Error
        );
    }


    /* public: perform a query */
    function free()
    {
        @mysqli_free_result($this->Query_ID);
        $this->Query_ID = 0;
    }

    /**
     * @return mysqli
     */
    function link_id()
    {
        return self::$Link_ID;
    }

    function query_id()
    {
        return $this->Query_ID;
    }

    /**
     * @param $queryString
     * @param $parameters
     * @return bool|int|mysqli_result
     */
    function preparedQuery($queryString,
                           $parameters
    )
    {
        /* No empty queries, please, since PHP4 chokes on them. */
        if ($queryString == "") /* The empty query string is passed on from the constructor,
             * when calling the class without a query, e.g. in situations
             * like these: '$db = new DB_Sql_Subclass;'
             */ return 0;
        if (!$this->connect()) {
            return 0; /* we already complained in connect() about that. */
        };
        # New query, discard previous result.
        if ($this->Query_ID) {
            $this->free();
        }
        if ($this->Debug) printf(
            "Debug: query = %s<br>\n",
            $queryString
        );
        $statement  = mysqli_prepare(
            self::$Link_ID,
            $queryString
        );
        $a_params   = array();
        $param_type = '';
        $n          = count($parameters);
        for ($i = 0; $i < $n; $i++) {
            $param_type .= $parameters[$i]['type'];
        }
        /* with call_user_func_array, array params must be passed by reference */
        $a_params[] = $param_type;
        for ($i = 0; $i < $n; $i++) {
            $a_params[] = &$parameters[$i]['value'];
        }
        call_user_func_array(
            array($statement, 'bind_param'),
            $a_params
        );
        if (!mysqli_stmt_execute($statement)) {
            $jsonParams = json_encode($parameters);
            $error      = mysqli_error(self::$Link_ID);
            throw new Exception("Failed to execute prepared query : {$error} $queryString, {$jsonParams}");
        }
        $result      = mysqli_stmt_get_result($statement);
        $this->Row   = 0;
        $this->Errno = mysqli_errno(self::$Link_ID);
        $this->Error = mysqli_error(self::$Link_ID);
        if ($this->Errno) {
            debug_print_backtrace();
            $this->halt("Invalid SQL: " . $queryString);
        }
        # Will return nada if it fails. That's fine.
        return $result;
    }

    /* public: walk result set */
    function next_record_object($className)
    {
        if (!$this->Query_ID) {
            $this->halt("next_record called with no query pending.");
            return 0;
        }
        $object      = @mysqli_fetch_object(
            $this->Query_ID,
            $className
        );
        $this->Row   += 1;
        $this->Errno = mysqli_errno(self::$Link_ID);
        $this->Error = mysqli_error(self::$Link_ID);
        return $object;
    }

    /* public: position in result set */
    function nextObjectRecord($className)
    {
        if (!$this->Query_ID) {
            $this->halt("next_record called with no query pending.");
            return 0;
        }
        $object      = @mysqli_fetch_object(
            $this->Query_ID,
            $className
        );
        $this->Row   += 1;
        $this->Errno = mysqli_errno(self::$Link_ID);
        $this->Error = mysqli_error(self::$Link_ID);
        return $object;
    }

    /* public: table locking */
    function fetchAll($fetchMode = MYSQLI_ASSOC)
    {
        if (!$this->Query_ID) {
            $this->halt("next_record called with no query pending.");
            return 0;
        }
        return mysqli_fetch_all($this->Query_ID, $fetchMode);
    }

    function seek($pos = 0)
    {
        $status = @mysqli_data_seek(
            $this->Query_ID,
            $pos
        );
        if ($status) $this->Row = $pos; else {
            $this->halt("seek($pos) failed: result has " . $this->num_rows() . " rows.");
            /* half assed attempt to save the day,
             * but do not consider this documented or even
             * desireable behaviour.
             */
            @mysqli_data_seek(
                $this->Query_ID,
                $this->num_rows()
            );
            $this->Row = $this->num_rows();
            return 0;
        }
        return 1;
    }

    /* public: evaluate the result (size, width) */
    function num_rows()
    {
        return @mysqli_num_rows($this->Query_ID);
    }

    function affected_rows()
    {
        return @mysqli_affected_rows(self::$Link_ID);
    }

    function num_fields()
    {
        return @mysqli_num_fields($this->Query_ID);
    }

    /* public: shorthand notation */
    function nf()
    {
        return $this->num_rows();
    }

    function np()
    {
        print $this->num_rows();
    }

    function p($Name)
    {
        if (isset($this->Record[$Name])) {
            print $this->Record[$Name];
        }
    }

    function nextid($seq_name)
    {
        $locked = false;
        /* if no current lock, lock sequence table */
        if (!$this->locked) {
            if ($this->lock($this->Seq_Table)) {
                $locked = true;
            } else {
                $this->halt("cannot lock " . $this->Seq_Table . " - has it been created?");
                return 0;
            }
        }
        /* get sequence number and increment */
        $q = sprintf(
        /** @lang text */ "SELECT nextid FROM %s WHERE seq_name = '%s'",
                          $this->Seq_Table,
                          $seq_name
        );
        if (!$this->query($q)) {
            $this->halt('query failed in nextid: ' . $q);
            return 0;
        }
        /* No current value, make one */
        if (!$this->next_record()) {
            $currentid = 0;
            $q         = sprintf(
            /** @lang text */ "INSERT INTO %s VALUES('%s', %s)",
                              $this->Seq_Table,
                              $seq_name,
                              $currentid
            );
            if (!$this->query($q)) {
                $this->halt('query failed in nextid: ' . $q);
                return 0;
            }
        } else {
            $currentid = $this->f("nextid");
        }
        $nextid = $currentid + 1;
        $q      = sprintf(
        /** @lang text */ "UPDATE %s SET nextid = '%s' WHERE seq_name = '%s'",
                          $this->Seq_Table,
                          $nextid,
                          $seq_name
        );
        if (!$this->query($q)) {
            $this->halt('query failed in nextid: ' . $q);
            return 0;
        }
        /* if nextid() locked the sequence table, unlock it */
        if ($locked) {
            $this->unlock();
        }
        return $nextid;
    }

    /* public: sequence numbers */
    function lock($table,
                  $mode = "write"
    )
    {
        $query = "lock tables ";
        if (is_array($table)) {
            foreach ($table as $key => $value) {
                // text keys are "read", "read local", "write", "low priority write"
                if (is_int($key)) $key = $mode;
                if (strpos(
                    $value,
                    ","
                )) {
                    $query .= str_replace(
                            ",",
                            " $key, ",
                            $value
                        ) . " $key, ";
                } else {
                    $query .= "$value $key, ";
                }
            }
            $query = substr(
                $query,
                0,
                -2
            );
        } elseif (strpos(
            $table,
            ","
        )) {
            $query .= str_replace(
                    ",",
                    " $mode, ",
                    $table
                ) . " $mode";
        } else {
            $query .= "$table $mode";
        }
        if (!$this->query($query)) {
            $this->halt("lock() failed.");
            return false;
        }
        $this->locked = true;
        return true;
    }

    /* public: return table metadata */
    function next_record($fetchMode = MYSQLI_BOTH)
    {
        if (!$this->Query_ID) {
            throw new Exception("next_record called with no query pending.");
            return 0;
        }
        $this->Record = @mysqli_fetch_array(
            $this->Query_ID,
            $fetchMode
        );
        $this->Row    += 1;
        $this->Errno  = mysqli_errno(self::$Link_ID);
        $this->Error  = mysqli_error(self::$Link_ID);
        $stat         = is_array($this->Record);
        if (!$stat && $this->Auto_Free) {
            $this->free();
        }
        return $stat;
    }

    /* public: find available table names */
    function f($Name)
    {
        if (isset($this->Record[$Name])) {
            return $this->Record[$Name];
        }
        return null;
    }

    /* private: error handling */
    function metadata($table = "",
                      $full = false
    )
    {
        $res = array();
        /*
         * Due to compatibility problems with Table we changed the behavior
         * of metadata();
         * depending on $full, metadata returns the following values:
         *
         * - full is false (default):
         * $result[]:
         *   [0]["table"]  table name
         *   [0]["name"]   field name
         *   [0]["type"]   field type
         *   [0]["len"]    field length
         *   [0]["flags"]  field flags
         *
         * - full is true
         * $result[]:
         *   ["num_fields"] number of metadata records
         *   [0]["table"]  table name
         *   [0]["name"]   field name
         *   [0]["type"]   field type
         *   [0]["len"]    field length
         *   [0]["flags"]  field flags
         *   ["meta"][field name]  index of field named "field name"
         *   This last one could be used if you have a field name, but no index.
         *   Test:  if (isset($result['meta']['myfield'])) { ...
         */ // if no $table specified, assume that we are working with a query
        // result
        if ($table) {
            $this->connect();
            $id = mysqli_query(
                self::$Link_ID,
                "select * from $table limit 1"
            );
            if (!$id) {
                $this->halt("Metadata query failed.");
                return false;
            }
        } else {
            $id = $this->Query_ID;
            if (!$id) {
                $this->halt("No query specified.");
                return false;
            }
        }
        $count = @mysqli_num_fields($id);
        // made this IF due to performance (one if is faster than $count if's)
        if (!$full) {
            for ($i = 0; $i < $count; $i++) {
                $fetchedObject = $id->fetch_field_direct($i);
                if ($fetchedObject) {
                    $res[$i]["table"] = $fetchedObject->table;
                    $res[$i]["name"]  = $fetchedObject->name;
                    $res[$i]["type"]  = $fetchedObject->type;
                    $res[$i]["len"]   = $fetchedObject->max_length;
                    $res[$i]["flags"] = $fetchedObject->flags;
                }
            }
        } else { // full
            $res["num_fields"] = $count;
            for ($i = 0; $i < $count; $i++) {
                $fetchedObject = $id->fetch_field_direct($i);
                if ($fetchedObject) {
                    $res[$i]["table"]              = $fetchedObject->table;
                    $res[$i]["name"]               = $fetchedObject->name;
                    $res[$i]["type"]               = $fetchedObject->type;
                    $res[$i]["len"]                = $fetchedObject->max_length;
                    $res[$i]["flags"]              = $fetchedObject->flags;
                    $res["meta"][$res[$i]["name"]] = $i;
                }

            }
        }
        // free the result only if we were called on a table
        if ($table) {
            @mysqli_free_result($id);
        }
        return $res;
    }

    function table_names()
    {
        $this->connect();
        $h      = @mysqli_query(
            self::$Link_ID,
            "show tables"
        );
        $i      = 0;
        $return = array();
        while ($info = @mysqli_fetch_row($h)) {
            $return[$i]["table_name"]      = $info[0];
            $return[$i]["tablespace_name"] = $this->Database;
            $return[$i]["database"]        = $this->Database;
            $i++;
        }
        @mysqli_free_result($h);
        return $return;
    }

}
