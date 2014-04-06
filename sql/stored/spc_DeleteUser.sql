delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_DeleteUser`(
	UsrId INTEGER
)
BEGIN

	DELETE FROM tbl_lnk_RolesUsers
	WHERE UserId = UsrId;

	DELETE FROM tbl_Users
	WHERE Id = UsrId;
END$$

