<?php
define( 'DAYS_TO_CREATE', 3620 ); // 10 years
define( 'DB_HOST', 'cncdevapps' ); 
define( 'DB_NAME', 'cncp1' ); 
define( 'DB_USER', 'root' ); 
define( 'DB_PASSWORD', 'CnC1989' );

ini_set( 'set_time_limit', 3600);
	
if (!$db=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
	echo 'Could not connect to mysql host ' . DB_HOST;
	exit;
}
mysql_select_db(DB_NAME, $db);

$query =
'create table date_lookup( date_field date NOT NULL, PRIMARY KEY ( date_field) )  ;';
	$result = mysql_query($query);

$this_date = strtotime( '-2 years', date( 'U' ) );

$end_date = strtotime ( '+' . DAYS_TO_CREATE . ' days', $this_date ) ; 

while( $this_date < $end_date ){

	$this_date = strtotime( "+1 day", $this_date );

	$query =
		'insert into date_lookup( date_field ) values ( "' . date( 'Y-m-d', $this_date ) . '" )  ;';
	$result = mysql_query($query);
	
}
?>