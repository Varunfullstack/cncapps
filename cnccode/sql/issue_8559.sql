INSERT INTO
	custitem_contract( cic_cuino, cic_contractcuino )
	SELECT
		cui_cuino,
		cui_contract_cuino
	FROM
		custitem
	WHERE
		cui_contract_cuino <> 0
