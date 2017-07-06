UPDATE
	custitem ci
	JOIN custitem co ON ci.`cui_contract_cuino` = co.`cui_cuino`
	JOIN item ON ci.cui_itemno = item.itm_itemno
SET
	ci.`cui_contract_cuino` = NULL
WHERE
	item.`itm_desc` LIKE "%DRAC%" OR
	item.`itm_desc` LIKE "%Hard Drive%" OR
	item.`itm_desc` LIKE "%PERC%" OR	
	item.`itm_desc` LIKE "%Controller%"					
