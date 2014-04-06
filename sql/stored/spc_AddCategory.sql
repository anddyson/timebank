delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_AddCategory`(IN Name VARCHAR(100))
BEGIN
	INSERT INTO tbl_Categories (Name) VALUES (Name);
END$$

