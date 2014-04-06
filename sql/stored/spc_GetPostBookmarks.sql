delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_GetPostBookmarks`(
    Id int
    ,UserId int
)
BEGIN
    SELECT *
    FROM tbl_PostBookmarks Pbm
    WHERE
        (Pbm.Id = Id OR Id IS NULL)
        AND (Pbm.UserId = UserId OR UserId IS NULL)
    ;
END$$

