SELECT
	*
FROM
	test_value_type tvt
	JOIN test_test_value_type ttvt ON ttvt.`testValueTypeId` = tvt.`testValueTypeid`
WHERE
	testId = 10