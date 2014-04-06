delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_DeleteMessage`(
    MsgId INT
    ,UserId INT
)
BEGIN

    UPDATE tbl_Messages Msg
    SET SenderDeleted = 1
    WHERE
        Msg.Id = MsgId
        AND Msg.SenderId = UserId;

    UPDATE tbl_Messages Msg
    SET ReceiverDeleted = 1
    WHERE
        Msg.Id = MsgId
        AND Msg.ReceiverId = UserId;

    -- if both sender and receiver have deleted it, then remove the entry completely
    DELETE FROM tbl_Messages
    WHERE
        Id = MsgId
        AND SenderDeleted = 1
        AND ReceiverDeleted = 1;
END$$

