<?
/**
* Replication from SCO UNIX DB text files to mySQL database tables.
*
* Replaces LINUX scripts named CopyFilesFromSCO.sh and CopyFilesFromSCO.sql and makes it OS independent
*	Should be automatically called as a timed process at intervals as required - synchronise with cron process
* Transaction running on SCO UNIX box under user express.
*
* Uses FTP to retrieve files
* Has been kept deliberately independent of normal Sweet Code class structure for portability
* and because certain mySQL-dependent calls required.
*
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
// Get list of table names to be processed from mySQL database
if (!mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
	echo 'Could not connect to mysql host ' . DB_HOST;
	exit;
}
$result = mysql_list_tables(REPLICATE_DB_NAME);
while ($row = mysql_fetch_row($result)) {
	$tableNames[]=$row[0];
}
mysql_free_result($result);
$ftp = ftp_connect(SCO_HOST);
if ($ftp){
	if (ftp_login($ftp, SCO_USER, SCO_PASSWORD)){
		foreach ($tableNames as $tableName) {
			if (ftp_size($ftp, SCO_REPLICATE_DIR . $tableName . '.txt') != -1) {		// Only process table if a text file found
				ftp_get(
					$ftp,
					LOCAL_REPLICATE_DIR . $tableName . '.txt',
					SCO_REPLICATE_DIR . $tableName . '.txt',
					FTP_BINARY
				);
				$result = mysql_query('DELETE FROM ' . $tableName);											// Clear down mySQL table
				$queryString = 																													// Load from text file into mySQL table
					'LOAD DATA INFILE \'' . LOCAL_REPLICATE_DIR . $tableName . '.txt\' INTO TABLE ' .
//					$tableName . ' FIELDS TERMINATED BY \'|\' ENCLOSED BY \'"\' ESCAPED BY \'\\\\\' LINES TERMINATED BY \'\n\'';
					$tableName . ' FIELDS TERMINATED BY \'|\' ESCAPED BY \'\\\\\' LINES TERMINATED BY \'\n\'';
				$result = mysql_query($queryString);
			}
		}
	}
	else{
		echo 'Could not log in to ftp server as user ' . SCO_USER;
	}
	ftp_close($ftp);
}
else{
	echo 'Could not connect to ftp server ' . SCO_HOST;
}
?>