delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_DeletePost`(
	PstId INT
)
BEGIN

	DELETE FROM tbl_lnk_CategoriesPosts WHERE PostId = PstId;

	DELETE FROM tbl_Posts WHERE Id = PstId;
END$$

