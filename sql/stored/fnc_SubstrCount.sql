delimiter $$

CREATE DEFINER=`root`@`%` FUNCTION `fnc_SubstrCount`(tag CHAR(20),s CHAR(20)) RETURNS char(50) CHARSET latin1
    DETERMINISTIC
RETURN round((LENGTH(tag) - LENGTH(REPLACE(tag,s, ''))) / LENGTH(','))$$

