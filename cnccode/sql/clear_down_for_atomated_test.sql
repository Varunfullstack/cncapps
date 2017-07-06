UPDATE automated_request SET importedFlag = "N";
DELETE FROM problem WHERE pro_problemno > 179566;
DELETE FROM callactivity WHERE caa_problemno > 179566;
