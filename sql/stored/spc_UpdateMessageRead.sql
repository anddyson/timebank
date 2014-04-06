delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateMessageRead`(
    Id INT
)
BEGIN
-- Definition statr 
UPDATE tbl_Messages Msg
SET ReadFlag = 1, ReadDateTime = NOW()
WHERE Msg.Id = Id;
-- Definition end
END$$

