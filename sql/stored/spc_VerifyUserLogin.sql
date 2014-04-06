delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_VerifyUserLogin`(
	Username VARCHAR(100)
	,Email VARCHAR(100)
	,Password VARCHAR(50)
)
BEGIN
	SELECT *
	FROM tbl_Users Usr
	WHERE
		(Usr.Username = Username OR Usr.Email = Email)
		AND Usr.Password = Password
		AND Usr.IsApproved = 1;

	# record a successful login
	UPDATE tbl_Users Usr
	SET Usr.LastLoginDateTime = NOW()
	WHERE
		(Usr.Username = Username OR Usr.Email = Email)
		AND Usr.Password = Password
		AND Usr.IsApproved = 1;

END$$

