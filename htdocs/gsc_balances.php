<?php
global $db;
$queryString =
	"SELECT cus_custno, cus_name, gscTopUpAmount, `curGSCBalance`, cui_cuino
	FROM `custitem` 
	RIGHT JOIN customer ON cui_custno = cus_custno
	WHERE `cui_itemno` = 4111 AND `cui_expiry_date` >= '2005-06-30' AND cus_custno <> 2511";

if (isset($_REQUEST['sort'])){
	$queryString .= "	ORDER BY ". $_REQUEST['sort'];
}
else{
	$queryString .= "	ORDER BY cus_name";
}
$db->query($queryString);
?>
<table class="singleBorder" width="400px" border="0" cellspacing="1" cellpadding="1">
	<tr> 
		<td class="tableHeader"><A HREF="<?php echo $_SERVER['PHP_SELF']; ?>?sort=cus_name">Customer</A></td>
		<td class="tableHeader"><A HREF="<?php echo $_SERVER['PHP_SELF']; ?>?sort=gscTopUpAmount">Top-up Amount</A></td>
		<td class="tableHeader"><A HREF="<?php echo $_SERVER['PHP_SELF']; ?>?sort=curGSCBalance">Balance</A></td>
	</tr>
	<?php
	$totalBalance = 0;
	$totalTopup = 0;
	while ( $db->next_record() ){
	?>
		<tr onMouseOver="this.bgColor='#FFFFCC';" onMouseOut="this.bgColor='';"> 
			<td><A HREF="<?php echo 'CustomerItem.php?action=displayCI&customerItemID=' . $db->Record['cui_cuino']; ?>"><?php echo $db->Record['cus_name'];?></A></td>
			<td><div align="right"><A HREF="<?php echo 'Customer.php?action=dispEdit&customerID=' . $db->Record['cus_custno']; ?>"><?php echo $db->Record['gscTopUpAmount'];?></A></div></td>
			<td><div align="right"><A HREF="<?php echo 'CustomerItem.php?action=displayCI&customerItemID=' . $db->Record['cui_cuino']	; ?>"><?php echo $db->Record['curGSCBalance'];?></A></div></td>
		</tr>
	<?php
		$totalBalance = $totalBalance + $db->Record['curGSCBalance'];
		$totalTopup = $totalTopup + $db->Record['gscTopUpAmount'];
	}
	?>
	<tr> 
		<td class="tableHeader">Total</td>
		<td class="listHeadNumber"><?php echo $totalTopup; ?></td>
		<td class="listHeadNumber"><?php echo $totalBalance; ?></td>
	</tr>
</table>