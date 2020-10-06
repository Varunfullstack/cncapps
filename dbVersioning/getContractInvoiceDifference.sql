DROP FUNCTION IF EXISTS `getContractInvoiceDifference`#
CREATE
    DEFINER = `root`@`::1` FUNCTION `getContractInvoiceDifference`(contractId INT, givenDate DATE) RETURNS int
    READS SQL DATA
    NOT DETERMINISTIC
BEGIN
    DECLARE formattedExpiryDate INTEGER;
    SELECT
        DATE_FORMAT(
                DATE_ADD(installationDate, INTERVAL totalInvoiceMonths - 1 MONTH),
                "%Y%m"
            ) INTO formattedExpiryDate
    FROM
        custitem
    WHERE custitem.`cui_cuino` = contractId;
    RETURN cast(PERIOD_DIFF(
            formattedExpiryDate,
            DATE_FORMAT(givenDate, "%Y%m")
         as integer));
END #
GRANT EXECUTE ON FUNCTION getContractInvoiceDifference TO 'webuser'@'%'