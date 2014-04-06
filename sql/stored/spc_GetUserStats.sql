delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetUserStats`(
	UserId INT
)
BEGIN

SELECT SUM(Hours) As Hours INTO @Hours FROM
	(
    SELECT Hours, GiverApproved, ReceiverApproved, IsDisputed FROM tbl_Transactions WHERE GiverId = UserId
	UNION ALL
	SELECT -(Hours), GiverApproved, ReceiverApproved, IsDisputed As Hours FROM tbl_Transactions WHERE ReceiverId = UserId
    ) AS TimeBank
WHERE GiverApproved = 1 AND ReceiverApproved = 1 AND IsDisputed = 0;

SELECT COUNT(Id) INTO @TransactionsPendingIncoming FROM tbl_Transactions
WHERE
	(GiverId = UserId AND GiverApproved = 0)
	OR (ReceiverId = UserId AND ReceiverApproved = 0);

SELECT COUNT(Id) INTO @TransactionsPendingOutgoing FROM tbl_Transactions
WHERE
	(GiverId = UserId AND ReceiverApproved = 0)
	OR (ReceiverId = UserId AND GiverApproved = 0);

SELECT COUNT(Id) INTO @WantedCount FROM tbl_Posts Pst
WHERE Pst.UserId = UserId AND Pst.Type = 1  AND Pst.ExpiryDate >= CURDATE();

SELECT COUNT(Id) INTO @OfferedCount FROM tbl_Posts Pst
WHERE Pst.UserId = UserId AND Pst.Type = 2 AND Pst.ExpiryDate >= CURDATE();

SELECT Count(Id) INTO @UnreadMessages FROM tbl_Messages
WHERE ReceiverId = UserId AND ReadFlag = 0;

IF @Hours IS NULL THEN SET @Hours = 0; END IF;

SELECT
	ROUND(@Hours, 1) As Hours
	,@WantedCount As WantedCount
	,@OfferedCount AS OfferedCount
	,@UnreadMessages As UnreadMessages
	,@TransactionsPendingIncoming AS TransactionsPendingIncoming
	,@TransactionsPendingOutgoing AS TransactionsPendingOutgoing
;
END$$

