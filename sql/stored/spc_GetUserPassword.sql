delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetUserPassword`(
	Identifier VARCHAR(100)
)
BEGIN

	SELECT Usr.Id, Usr.FirstName, Usr.LastName, Usr.Email, Usr.Password
	FROM tbl_Users Usr
	WHERE
		Usr.Username = Identifier
		OR Usr.Email = Identifier;
END$$

