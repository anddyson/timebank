delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateUserPassword`(
	Id INTEGER
	,Password VARCHAR(50)
)
BEGIN
	UPDATE tbl_Users Usr
	SET Usr.Password = Password
	WHERE Usr.Id = Id;
END$$

