delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_GetCategories`(
	CatId INT
	,PostId INT
)
BEGIN
	IF CatId IS NOT NULL THEN
		SELECT *
		FROM tbl_Categories Cat
		WHERE Cat.Id = CatId;
	ELSEIF PostId IS NOT NULL THEN
		SELECT Cat.*
		FROM tbl_Categories Cat
		INNER JOIN tbl_lnk_CategoriesPosts Ctp ON Cat.Id = Ctp.CategoryId
		WHERE Ctp.PostId = PostId
		ORDER BY Cat.Id;
    ELSE
        SELECT *
        FROM tbl_Categories Cat
		ORDER BY Cat.Name;
	END IF;
END$$

