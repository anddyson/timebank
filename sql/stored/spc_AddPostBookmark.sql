delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_AddPostBookmark`(
    UserId int
    ,PostId int
)
BEGIN

    SELECT @UsrId = UserId FROM tbl_PostBookmarks Pbm WHERE Pbm.UserId = UserId AND Pbm.PostId = PostId;
    IF @UsrId IS NULL THEN
        INSERT INTO tbl_PostBookmarks
        (UserId, PostId)
        VALUES (UserId, PostId);
    END IF;
END$$

