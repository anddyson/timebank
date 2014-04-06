delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_DeleteCategory`(
	CatId INTEGER
)
BEGIN

	DELETE FROM tbl_lnk_CategoriesPosts
	WHERE CategoryId = CatId;

	DELETE FROM tbl_Categories
	WHERE Id = CatId;
END$$

