delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetMessages`(
	MsgId INT
	,SenderId INT
	,ReceiverId INT
	,ReadFlag BOOL
)
BEGIN
	SELECT * FROM tbl_Messages Msg
	WHERE
		(Msg.Id = MsgId OR MsgId IS NULL)
		AND ((Msg.SenderId = SenderId AND SenderDeleted = 0) OR SenderId IS NULL)
		AND ((Msg.ReceiverId = ReceiverId AND ReceiverDeleted = 0) OR ReceiverId IS NULL)
		AND (Msg.ReadFlag = ReadFlag OR ReadFlag IS NULL)
	ORDER BY Msg.SentDateTime DESC;
END$$

