delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_UpdatePost`(
	Id INT
	,Heading VARCHAR(100)
	,Description VARCHAR(1000)
	,UserId INT
	,Type INT
	,ExpiryDate DATE
	,CategoryIds VARCHAR(4000)
)
BEGIN

	UPDATE tbl_Posts Pst
	SET
	Pst.Heading = Heading
	,Pst.Description = Description
	,Pst.UserId = UserId
	,Pst.UpdatedDate = CURDATE()
	,Pst.Type = Type
	,Pst.ExpiryDate = ExpiryDate
	WHERE Pst.Id = Id;

	DELETE FROM tbl_lnk_CategoriesPosts WHERE PostId = Id;
	CALL spc_SplitString(CategoryIds, ',');
	INSERT INTO tbl_lnk_CategoriesPosts (CategoryId, PostId) SELECT split_value, Id AS 'PostId' FROM splitResults;

	
END$$

