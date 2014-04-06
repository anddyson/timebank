delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetPostsBookmarked`(
	UserId int
)
BEGIN
	SELECT Pst.*
	FROM tbl_Posts Pst
	INNER JOIN tbl_PostBookmarks Pbm ON Pst.Id = Pbm.PostId
	WHERE Pbm.UserId = UserId;
END$$

