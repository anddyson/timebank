delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetTransactionNotifications`(
	UserId INT
)
BEGIN

SELECT Id AS TransactionId, ReceiverId As OtherUserId, 1 As Type, Hours, Description, TransactionDateTime, Uuid FROM tbl_Transactions
WHERE (GiverId = UserId AND GiverApproved = 0)
UNION
SELECT Id AS TransactionId, GiverId As OtherUserId,  2 As Type, Hours, Description, TransactionDateTime, Uuid FROM tbl_Transactions
WHERE (ReceiverId = UserId AND ReceiverApproved = 0);
	
END$$

