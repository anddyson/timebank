delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_AddMessage`(
    SenderId INT
    ,ReceiverId INT
    ,Subject VARCHAR(200)
    ,Body VARCHAR(4000)
    ,ReadFlag BOOLEAN
    ,PostId INT
)
BEGIN
-- Definition start

IF ReadFlag IS NULL THEN
    SET ReadFlag = 0;
END IF;

INSERT INTO tbl_Messages
(
    SenderId
    ,ReceiverId
    ,Subject
    ,Body
    ,SentDateTime
    ,ReadDateTime
    ,ReadFlag
    ,PostId
)
VALUES
(
    SenderId
    ,ReceiverId
    ,Subject
    ,Body
    ,NOW()
    ,NULL
    ,ReadFlag
    ,PostId
);
END$$

