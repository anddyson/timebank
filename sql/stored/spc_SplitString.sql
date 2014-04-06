delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_SplitString`(
	x varchar(255)
	,delim varchar(12)
)
BEGIN
	SET @Valcount = fnc_SubstrCount(x,delim)+1;
	SET @v1 = 0;
	DROP TABLE IF EXISTS splitResults;
	CREATE TEMPORARY TABLE splitResults (split_value varchar(255));
	WHILE (@v1 < @Valcount) DO
		SET @val = fnc_SplitString(x,delim,@v1+1);
		INSERT INTO splitResults (split_value) VALUES (@val);
		SET @v1 = @v1 + 1;
	END WHILE;
END$$

