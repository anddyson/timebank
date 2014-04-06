delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_AddPost`(
	Heading VARCHAR(100)
	,Description VARCHAR(1000)
	,UserId INT
	,Type INT
	,ExpiryDate DATE
	,CategoryIds VARCHAR(4000)
)
BEGIN
	DECLARE NewId INT;

	INSERT INTO tbl_Posts
	(
		Heading
		,Description
		,UserId
		,CreatedDate
		,UpdatedDate
		,Type
		,ExpiryDate
	)
	VALUES
	(
		Heading
		,Description
		,UserId
		,CURDATE()
		,NULL
		,Type
		,ExpiryDate
	);

SELECT LAST_INSERT_ID() INTO NewId;
#	SELECT @PostId = last_insert_id();
	CALL spc_SplitString(CategoryIds, ',');
	#INSERT INTO tbl_lnk_CategoriesPosts (CategoryId, PostId) SELECT split_value, @PostId AS 'PostId' FROM splitResults;
	INSERT INTO tbl_lnk_CategoriesPosts (CategoryId, PostId) SELECT split_value, NewId AS 'PostId' FROM splitResults;
END$$

