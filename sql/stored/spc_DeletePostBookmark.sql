delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_DeletePostBookmark`(
    PbmId int
    ,UsrId int
    ,PstId int
)
BEGIN
    IF PbmId IS NOT NULL THEN
        DELETE FROM tbl_PostBookmarks WHERE Id = PbmId;
    ELSEIF PstId IS NOT NULL AND UsrId IS NOT NULL THEN
        DELETE FROM tbl_PostBookmarks WHERE UserId = UsrId and PostId = PstId;
    END IF;
END$$

