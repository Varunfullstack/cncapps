<?php
/**
*	MySQL database classes
*
* Include methods and classes for caching entire result sets into a PHP array
*
*	@package sc
* @author Karim Ahmed
* @version 1.0
* @copyright Sweetcode Ltd 2005
* @public
*/
require_once($_SERVER['DOCUMENT_ROOT'] . '/.config.php');
require_once(CONFIG_PATH_SC_CLASSES . 'object.php');
require_once(CONFIG_PATH_SC_CLASSES . 'string.php');
define('SC_MYSQL_FIELD_VALUE_MARKER', '~');
/**
*	DB_MysqlConnection
*
* Database connection class
*
* Author: Karim Ahmed
* (C) 2005 Sweet Code Ltd
* @access		public
* @version	1.0
*/
class SC_MysqlConnection extends SC_Object{
	/**
	*	$user
	*
	*	database username
	*
	* @access protected
	*/
	var $user;
	/**
	*	$pass
	*
	*	database password
	*
	* @access protected
	*/
	var $pass;
	/**
	*	$db_host
	*
	*	MySQL database server
	*
	* @access protected
	*/
	var $db_host;
	/**
	*	$db_name
	*
	*	Database name
	*
	* @access protected
	*/
	var $db_name;
	/**
	*	$connection
	*
	*	Database connection handle
	*
	* @access protected
	*/
	var $connection;
	/**
	*	SC_MysqlConnection
	*
	* PHP V4/5 cross-compatibility
	* @param	string	$user			db user name
	* @param	string	$pass			password
	* @param	string	$db_host	MySQL server host (e.g. www.myhost.com)
	* @return string	$db_name	database name in server
	* @access public
	* @version	1.0
	*/
	function SC_MysqlConnection($user, $pass, $db_host, $db_name) {
		$this->__construct($user, $pass, $db_host, $db_name);
	}
	/**
	*	__construct
	*
	* @param	string	$user			db user name
	* @param	string	$pass			password
	* @param	string	$db_host	MySQL server host (e.g. www.myhost.com)
	* @return string	$db_name	database name in server
	* @access public
	*/
	function __construct($user, $pass, $db_host, $db_name) {
		parent::__construct();
		$this->user		= $user;
		$this->pass		= $pass;
		$this->db_host	= $db_host;
		$this->db_name	= $db_name;
	}
	/**
	*	connect
	*
	*	Create a connection to a MySQL server, select a database and set the connection
	* time zone.
	*
	* @access protected
	* @return void
	*/
	function connect() {
		$this->connection = mysql_pconnect($this->db_host, $this->user, $this->pass);
		if(!is_resource($this->connection)) {
			die('Could not connect to server');			// Somthing wrong with the server 
		}
		if(!mysql_select_db($this->db_name, $this->connection)) {
			die('Could not connect to database');		// something wrong with the database
		}
		$this->setConnectionTimeZone();					
	}
	/**
	*	last_id
	*
	*	Last generated PK ID on this connection.
	* @access public
	* @return Integer last_id
	*/
	function lastID()
	{
		return mysql_insert_id($this->connection);
	}
	/**
	*	execute
	*
	*	Execute an SQL query against the selected database.
	* Use this instead of MysqlStatement::execute() when there are no parameters for
	* the query.
	*
	* @access public
	* @return MysqlStatement Statement
	*/
	function execute($query) {
		if(!$this->connection) {					// create a DB  if does not exist
				$this->connect();
		}
		$ret = mysql_query($query, $this->connection); 
		if(!$ret) {								// something wrong with the query
			return false;						
		}
		else if(!is_resource($ret)) {
			return false;						// something else went wrong
		}
		else {
			$stmt = new SC_MysqlStatement($this->connection, $query);
			$stmt->result = $ret;
			return $stmt;						// yeah baby!
		}
	}
	/**
	* @access public
	*
	* Not sure this is needed??
	*/
	function prepare($query) {
		if(!$this->connection) {
			$this->connect();
		}
		$ret = new SC_MysqlStatement($this->connection, $query);
		return $ret;
	}
	/**
	* setConnectionTimeZone(time_zone)
	*
	* set DB connection time (default BST) only for this connection.
	*
	* @access public
	*/
	function setConnectionTimeZone($time_zone = 'BST')
	{
		$this->execute("SET time_zone = $time_zone");
	}	
} // end of class DBMysql

/**
* MysqlStatement
*
* SQL statement class
*
* mySQL statement class handles operations on SQL statements
* Author: Karim Ahmed
* (C) 2005 Sweet Code Ltd
* @access Static
* @version	1.0
*/
class SC_MysqlStatement extends SC_Object{
	/**
	* DB_Result $result	SQL result set instance
	*
	* @access public
	*/
	var $result;
	/**
	* array $value_binds Query parameters to be bound to statement
	*
	* @access public
	*/
	var $value_binds;
	/**
	* string $query Query SQL query string
	*
	* @access public
	*/
	var $query;
	/**
	* array $binds Query parameters to be bound to statement
	*
	* @access public
	*/
	var $connection;
	/**
	* @access public
	*/
	function SC_MysqlStatement(&$connection, $query) {
		$this->__construct($connection, $query);
	}
	/**
	* @access public
	*/
	function __construct(&$connection, $query) {
		parent::__construct();
		if(!is_resource($connection)) {
			die("Not a valid database ");
		}
		if ($query == '') {
			die("No query passed");
		}
		$this->query = $query;
		$this->connection = & $connection;
	}
	/**
	* IS IT USED THOUGH???!!
	* @access private
	*/
	function _bindParam($ph, $pv) {
		$this->value_binds[$ph] = $pv;
	}
	/**
	* @param array Query parameters to be bound to the prepared statement
	* @access public
	*/
	function execute() {
		// do the value binds
		$query = $this->query;

		$binds = func_get_args();
		
		if (
			func_num_args() > 0	&&			// there are parameters to bind
			$binds[0] !== false
		){

			if ( func_num_args() == 1 && is_array($binds[0])){
				$binds = $binds[0];
			}
			foreach($binds as $index => $name) {
				$this->value_binds[$index + 1] = $name;
			}
			$cnt = count($binds);
			/*
			Bind each parameter to it's placeholder in the prepared statement (e.g. :1 )
			*/
			if ($cnt > 0) {
				foreach ($this->value_binds as $ph => $pv) {
					if ( is_null($pv) ){
						$query = SC_String::strReplaceFirst(
							SC_MYSQL_FIELD_VALUE_MARKER ."$ph",
							'null',
							$query
						);
					}
					else{
						$query = SC_String::strReplaceFirst(
							SC_MYSQL_FIELD_VALUE_MARKER ."$ph",
							"'".mysql_escape_string($pv)."'",
							$query
						);
					}
				}
			}
		}
		$start_time = microtime();
		if (SC_HTTP::sessionVar('show_queries')){
/*
			$duration = microtime() - $start_time;
			@$_SESSION['queries'] .= '<HR><PRE>Query(' .  $duration . ' seconds): '. CR . trim($query) . '</PRE>';
*/
			echo "<PRE>".$query."</PRE>";
		}
		$this->result = @mysql_query( $query, $this->connection );
		if ( !$this->result ){						// is false on error
			parent::raiseError('Database Error<BR><BR>Statement: ' . $query . '<BR><BR>Error: ' . $this->getError());
		}
		else{
			return true;
		}
	}
	function getError()
	{
		return mysql_error();
	}
	/**
	* @access public
	*/
	function fetchRow() {
		if(!$this->result) {
			die("Query not executed");
		}
		return mysql_fetch_row($this->result);
	}
	/**
	* @access public
	*/
	function fetchAssoc() {
		return mysql_fetch_assoc($this->result);
	}
	/**
	* fetchAllAssoc
	*
	* Build an array of result rows. Allows us to reuse this connection
	* within nested loops of query results etc.
	*
	* @access	public
	* @return	array Array of result set rows
	*/
	function fetchAllAssoc() {
		$retval = array();
		while($row = $this->fetchAssoc()) {
			$retval[] = $row;
		}
		return $retval;
	}
	/**
	*	numRows
	*
	* @access public
	* @return	integer	number of result set rows
	*/
	function numRows()
	{
		return @mysql_num_rows($this->result);
	}
	/**
	*	foundRows
	*
	* @access public
	* @return	integer	number of result set rows EXCLUDING LIMITs
	*/
	function foundRows()
	{
		$row = mysql_fetch_row(@mysql_query( 'SELECT FOUND_ROWS()' , $this->connection ));
		return $row[0];
	}
	
} // end of class MysqlStatement

/**
* SC_DB_Result_Cache
*
* class operates on a result set cached into a PHP array
* using DB_MySQLStatement->fetchAllAssoc()
*
* Probably best used where the result set is not huge (for memory reasons)
* and maybe only when the result set needs to be cached for some reason. e.g.
* more than one parse or in order to reuse a DB connection within nested query
* loops.
*
* @access Public
* @version	1.0
*/
class SC_DBResultCache extends SC_Object{
	/**
	* @access protected
	*/
	var $stmt;
	/**
	* @access protected
	*/
	var $result = array();
	/**
	* @access private
	*/
	var $row_index = 0;
	/**
	* @access private
	*/
	var $curr_index = 0;
	/**
	* @access private
	*/
	var $done = false;
	/**
	* @access public
	* @param	MysqlStatement $stmt	Pre-executed statement object
	*/
	function SC_DBResultCache (&$stmt) {
		$this->__construct($stmt);
	}
	/**
	* @access public
	*/
	function __construct(&$stmt) 
	{
		parent::__construct();
		$this->stmt = &$stmt;
	}
	/**
	* @access public
	*/
	function first() {
		if(!$this->result) {
			$this->result[$this->row_index++] = $this->stmt->fetchAssoc();
		}
		$this->curr_index = 0;
		return $this;
	}
	/**
	* @access public
	*/
	function last() {
		if(!$this->done) {
			array_push($this->result, $this->stmt->fetchAllAssoc());
		}
		$this->done = true;
		$this->curr_index = $this->row_index = count($this->result) - 1;
		return $this;
	}
	/**
	* @access public
	*/
	function next()
	{
		if($this->done) {
			return false;
		}
		$offset = $this->curr_index + 1;
		if(!$this->result[$offset]) {
			$row = $this->stmt->fetchAssoc();
			if(!$row) {
				$this->done = true;
				return false;
			}
			$this->result[$offset] = $row;
			++$this->row_index;
			++$this->curr_index;
			return $this;
		}
		else {
			++$this->curr_index;
			return $this;
		}
	}
	/**
	* @access public
	*/
	function prev()
	{
		if($this->curr_index == 0) {
			return false;
		}
		--$this->curr_index;
		return $this;
	}
	/**
	* @access protected
	* NOT SURE WHAT THIS IS FOR!!
	*/
	function __get($value) {
		if(array_key_exists($value, $this->result[$this->curr_index])) {
			return $this->result[$this->curr_index][$value];
		}
	}
} // end of class SC_DBResultCache
?>