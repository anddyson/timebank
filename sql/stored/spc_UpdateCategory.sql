delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateCategory`(
	Id INTEGER
	,Name VARCHAR(100)
)
BEGIN
	UPDATE tbl_Categories Cat
	SET Cat.Name = Name
	WHERE Cat.Id = Id;
END$$

