delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateMessageUnread`(
    Id INT
)
BEGIN
-- Definition statr 
UPDATE tbl_Messages Msg
SET ReadFlag = 0, ReadDateTime = NULL
WHERE Msg.Id = Id;
-- Definition end
END$$

