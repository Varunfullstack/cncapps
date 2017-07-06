DROP VIEW auto_request_contracts;
CREATE VIEW auto_request_contracts AS
SELECT 
  cui_cuino AS contractID,
  cui_custno AS customerID,
  renewalType.description AS renewalType,
  add_postcode AS postcode,
  itm_desc AS description,
  adslPhone AS adslPhone
FROM
  custitem 
  JOIN item 
    ON cui_itemno = itm_itemno 
  JOIN renewalType 
    ON renewalType.renewalTypeID = item.renewalTypeID 
  JOIN address 
    ON add_siteno = cui_siteno AND add_custno = cui_custno 
 WHERE
    renewalType.allowSrLogging = 'Y' 
    AND declinedFlag <> 'Y' 
ORDER BY renewalType.description,
  itm_desc;