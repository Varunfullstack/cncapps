SELECT
	odl_desc,
	SUM(ordline.`odl_qty_ord`)
FROM
	ordline
WHERE
	odl_desc LIKE '%- consultancy'
GROUP BY
	odl_desc
	