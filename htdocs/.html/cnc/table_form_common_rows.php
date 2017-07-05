<?php
	$fields = array(
		$business->getTableName() . '.modified_by_user_id',
		$business->getTableName() . '.modified_date',
		$business->getTableName() . '.created_by_user_id',
		$business->getTableName() . '.created_date'
	);
	foreach ($fields as $field) {
		require(CONFIG_PATH_MPM_HTML . 'table_info_row.php');				
	}
?>
