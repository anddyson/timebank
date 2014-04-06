delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_AddUser`(
	FirstName VARCHAR(100)
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
	INSERT INTO tbl_Users
	(
		FirstName
		,LastName
		,Email
		,Phone
		,Address
		,Postcode
		,IsApproved
		,LastLoginDateTime
		,ReminderCount
		,IsActive
		,Password
		,Username
		,UUid
        ,MailingListMember
	)
	VALUES
	(
		FirstName
		,LastName
		,Email
		,Phone
		,Address
		,Postcode
		,IsApproved
		,LastLoginDateTime
		,ReminderCount
		,IsActive
		,Password
		,Username
		,UUID()
        ,MailingListMember
	);

	# return the user with details, including the UUID, completed
	SELECT Id, FirstName, LastName, Email, Username, UUid FROM tbl_Users
	WHERE Id = last_insert_id();
END$$

