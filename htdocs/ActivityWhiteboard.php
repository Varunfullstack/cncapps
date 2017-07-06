<?php
/*
Call count by engineer for today

For plasma whiteboard
*/
require_once("config.inc.php");

function add_to_consultants_array( $result, $key ){

	global $consultants;

	if ( mysql_num_rows( $result ) ){
		while($i = mysql_fetch_assoc($result)) {
			$result_array[] = $i;
		}
		
		foreach ( $consultants AS $c_key => $c_row ){
	
			foreach ( $result_array AS $result_key => $result_row ) {
	
				if ( $c_row['cns_consno' ] == $result_row['cns_consno'] ){
					$consultants[$c_key][$key] = $result_row['hours'];  
				}
	
			}
	
		}
	}
}

if (!$db=mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
	echo 'Could not connect to mysql host ' . DB_HOST;
	exit;
}
mysql_select_db(DB_NAME, $db);
// array of users
// Exclude Karim and customers
$query = "
	SELECT
		cns_consno,
		cns_name,
		'0' as total_today,
		'0' as charge_today,
		'0' as total_mtd,
		'0' as charge_mtd
	FROM consultant
	WHERE
		activeFlag = 'Y'
		AND customerID = 0
		AND cns_consno <> 1
		AND cns_consno <> 30
		AND cns_consno <> 44
	";

$result = mysql_query($query);

while($i = mysql_fetch_assoc($result)) { 
	$consultants[] = $i;
}

// Chargable today
$query = "
	SELECT
		cns_consno,
		cns_name,
		SUM((TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) as hours
	FROM callactivity
		JOIN consultant ON caa_consno = cns_consno
		JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
		JOIN item ON itm_itemno = cat_itemno
	WHERE caa_date = CURDATE()
		AND itm_sstk_price > 0
	GROUP BY cns_consno
	";

$result = mysql_query($query);

add_to_consultants_array( $result, 'charge_today' );

// Total today
$query = "
	SELECT
		cns_consno,
		cns_name,
		SUM((TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) as hours
	FROM callactivity
		JOIN consultant ON caa_consno = cns_consno
		JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
		JOIN item ON itm_itemno = cat_itemno
	WHERE caa_date = CURDATE()
	GROUP BY cns_consno
	";

$result = mysql_query($query);

add_to_consultants_array( $result, 'total_today' );

// Chargable MTD
$query = "
	SELECT
		cns_consno,
		cns_name,
		SUM((TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) as hours
	FROM callactivity
		JOIN consultant ON caa_consno = cns_consno
		JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
		JOIN item ON itm_itemno = cat_itemno
	WHERE caa_date > DATE_SUB( CURDATE(), INTERVAL DAYOFMONTH( CURDATE() ) DAY )
		AND itm_sstk_price > 0
	GROUP BY cns_consno
	";

$result = mysql_query($query);

add_to_consultants_array( $result, 'charge_mtd' );

// Total MTD
$query = "
	SELECT
		cns_consno,
		cns_name,
		SUM((TIME_TO_SEC( caa_endtime ) - TIME_TO_SEC( caa_starttime )) /3600) as hours
	FROM callactivity
		JOIN consultant ON caa_consno = cns_consno
		JOIN callacttype ON cat_callacttypeno = caa_callacttypeno
		JOIN item ON itm_itemno = cat_itemno
	WHERE caa_date > DATE_SUB( CURDATE(), INTERVAL DAYOFMONTH( CURDATE() ) DAY )
	GROUP BY cns_consno
	";

$result = mysql_query($query);

add_to_consultants_array( $result, 'total_mtd' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<META HTTP-EQUIV=REFRESH CONTENT=40>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Activities Today</title>
<link href="screen.css" rel="stylesheet" type="text/css" />
</head>

<body>
<table width="100%" border="0" cellspacing="2" cellpadding="2">
  <tr>
    <th scope="col">&nbsp;</th>
    <th colspan="2" bgcolor="#CCCCFF" scope="col">Today</th>
    <th scope="col">&nbsp;</th>
    <th colspan="2" bgcolor="#CCCCFF" scope="col">Month to Date </th>
  </tr>
  <tr>
    <th scope="row">&nbsp;</th>
    <th bgcolor="#FFFFCC">Total</th>
    <th bgcolor="#FFFFCC">Chargable</th>
    <th>&nbsp;</th>
    <th bgcolor="#FFFFCC">Total</th>
    <th bgcolor="#FFFFCC">Chargable</th>
  </tr>
	<?php
	$g_total_today 	= 0;
	$g_charge_today = 0;
	$g_total_mtd 		= 0;
	$g_charge_mtd 	= 0;

	foreach ( $consultants AS $key => $row ){
	?>
		<tr>
			<td align="left" bgcolor="#F0F0F0" class="fieldRight" scope="row"><?php echo $row['cns_name'] ?></td>
			<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($row['total_today'], 1) ?></div></td>
			<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($row['charge_today'], 1) ?></div></td>
			<td bgcolor="#F0F0F0"><div align="right"></div></td>
			<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($row['total_mtd'], 1 ) ?></div></td>
			<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($row['charge_mtd'], 1) ?></div></td>
		</tr>
		<?php
		$g_total_today += $row['total_today'];
		$g_charge_today += $row['charge_today'];
		$g_total_mtd += $row['total_mtd'];
		$g_charge_mtd += $row['charge_mtd'];
	}
	?>
	<tr>
		<td colspan="6">&nbsp;
		
		</td>
	</tr>
	<tr>
		<td align="left" bgcolor="#F0F0F0" class="fieldRight" scope="row">Grand Totals</td>
		<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($g_total_today, 1) ?></div></td>
		<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($g_charge_today, 1) ?></div></td>
		<td bgcolor="#F0F0F0"><div align="right"></div></td>
		<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($g_total_mtd, 1 ) ?></div></td>
		<td bgcolor="#F0F0F0"><div align="right"><?php echo number_format($g_charge_mtd, 1) ?></div></td>
	</tr>
</table>
<p>&nbsp;</p>
</body>
</html>
