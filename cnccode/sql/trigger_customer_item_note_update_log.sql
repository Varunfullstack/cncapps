DELIMITER $$ 

DROP TRIGGER /*!50032 IF EXISTS */ `cncp1`.`custitem_update`$$ 

CREATE 
/*!50017 DEFINER = 'root'@'localhost' */ 
TRIGGER `custitem_update` AFTER UPDATE ON `custitem` 
FOR EACH ROW BEGIN 

	DECLARE done INT DEFAULT 0; 

	DECLARE colname VARCHAR(500);	

	DECLARE insertStatement VARCHAR(500);	
	/* Can't do this 
	DECLARE curColName CURSOR FOR
		SELECT
			COLUMN_NAME as colname
		FROM
			`information_schema`.`COLUMNS`
		WHERE
			TABLE_SCHEMA = 'cncp1' AND TABLE_NAME = 'custitem';
			 
	DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1; 

	OPEN curColName; 
	REPEAT 
		FETCH curColName INTO colname; 
	*/
		
		IF ( NEW.notes != OLD.notes ) THEN
		
			INSERT INTO
				audit_trail(
					colName,
					oldValue,
					newValue,
					modifyDate
				) VALUES(
					'notes',
					OLD.notes,
					NEW.notes,
					NOW()
				);
				
		END IF;
/*		
	UNTIL done END REPEAT; 
	CLOSE curColName;	
*/
END; 
$$ 

DELIMITER ; 