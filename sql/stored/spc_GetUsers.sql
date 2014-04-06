delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_GetUsers`(
	UsrId INTEGER
	,SearchString VARCHAR(200)
	,BasicOnly BOOLEAN
)
BEGIN
	IF BasicOnly = False OR BasicOnly IS NULL THEN
	SELECT
		Id
		,FirstName
		,LastName
		,Email
		,Address
		,Postcode
		,IsApproved
		,LastLoginDateTime
		,ReminderCount
		,IsActive
		,Phone
		,Username
        ,MailingListMember
	FROM tbl_Users Usr
	WHERE
		(Usr.Id = UsrId OR UsrID IS NULL)
		AND
		(
			CONCAT(Usr.FirstName, " ", Usr.LastName, Usr.Username, Usr.Email) LIKE SearchString
			OR
			SearchString IS NULL
		);
	ELSE
	SELECT
		Id
		,FirstName
		,LastName
		,Username
	FROM tbl_Users Usr
	WHERE
		(Usr.Id = UsrId OR UsrID IS NULL)
		AND
		(
			CONCAT(Usr.FirstName, " ", Usr.LastName, Usr.Username, Usr.Email) LIKE SearchString
			OR
			SearchString IS NULL
		);
	END IF;
END$$

