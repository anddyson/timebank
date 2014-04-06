delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateUser`(
	Id INTEGER
	,FirstName VARCHAR(100)
	,LastName VARCHAR(100)
	,Email VARCHAR(100)
	,Phone VARCHAR(100)
	,Address VARCHAR(500)
	,Postcode VARCHAR(10)
	,IsApproved BOOLEAN
	,LastLoginDateTime DATETIME
	,ReminderCount INTEGER
	,IsActive BOOLEAN
	,Password VARCHAR(50)
	,Username VARCHAR(100)
    ,MailingListMember BOOLEAN
)
BEGIN
	UPDATE tbl_Users Usr
	SET
		Usr.FirstName = FirstName
		,Usr.LastName = LastName
		,Usr.Email = Email
		,Usr.Phone = Phone
		,Usr.Address = Address
		,Usr.Postcode = Postcode
        ,Usr.MailingListMember = MailingListMember
	WHERE Usr.Id = Id;

	IF IsApproved IS NOT NULL THEN
		UPDATE tbl_Users Usr
		SET Usr.IsApproved = IsApproved
		WHERE Usr.Id = Id;
	END IF;

	IF IsActive IS NOT NULL THEN
		UPDATE tbl_Users Usr
		SET Usr.IsActive = IsActive
		WHERE Usr.Id = Id;
	END IF;

	IF Password IS NOT NULL THEN
		UPDATE tbl_Users Usr
		SET Usr.Password = Password
		WHERE Usr.Id = Id;
	END IF;

	IF Username IS NOT NULL THEN
		UPDATE tbl_Users Usr
		SET Usr.Username = Username
		WHERE Usr.Id = Id;
	END IF;
END$$

