delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetPosts`(
	PostId INTEGER
	,UserId INTEGER
	,SearchString VARCHAR(200)
	,Wanted BOOLEAN
	,Offered BOOLEAN
	,DateFrom DATE
	,CategoryIds VARCHAR(4000)
	,ShowExpired BOOL
    ,LogSearch BOOL
)
BEGIN
	DECLARE PostType VARCHAR(3);

	SET @qry = 'SELECT DISTINCT Pst.* FROM tbl_Posts Pst
	LEFT OUTER JOIN tbl_lnk_CategoriesPosts Ctp ON Pst.Id = Ctp.PostId
	WHERE 1 = 1';

	IF CategoryIds IS NOT NULL AND CategoryIds <> '' THEN
		SET @qry = CONCAT(@qry,' AND Ctp.CategoryId IN (',CategoryIds,')');
	ELSEIF CategoryIds = '' THEN
		SET @qry = CONCAT(@qry,' AND Ctp.CategoryId = 0');
	END IF;
	IF PostId IS NOT NULL THEN SET @qry = CONCAT(@qry,' AND Pst.Id = ',PostId); END IF;
	IF UserId IS NOT NULL THEN SET @qry = CONCAT(@qry,' AND Pst.UserId = ',UserId); END IF;
	IF SearchString IS NOT NULL THEN SET @qry = CONCAT(@qry,' AND CONCAT(Pst.Heading,Pst.Description) LIKE \'',SearchString,'\''); END IF;
	IF DateFrom IS NOT NULL THEN SET @qry = CONCAT(@qry,' AND Pst.CreatedDate >= \'',DateFrom,'\''); END IF;
	IF ShowExpired = 0 THEN SET @qry = CONCAT(@qry,' AND Pst.ExpiryDate >= CURDATE()'); END IF;
	IF Wanted = 1 OR Offered = 1 THEN
		SET PostType = '';
		IF Wanted = 1 THEN SET PostType = '1'; END IF;
		IF Wanted = 1 AND Offered = 1 THEN SET PostType = CONCAT(PostType, ','); END IF;
		IF Offered = 1 THEN SET PostType = CONCAT(PostType, '2'); END IF;
		SET @qry = CONCAT(@qry,' AND Pst.Type IN (',PostType,')');
	ELSEIF Wanted = 0 AND Offered = 0 THEN #note this isn't the same as them both being NULL
		SET @qry = CONCAT(@qry, ' AND Pst.Type  = 0');
	END IF;
	SET @qry = CONCAT(@qry, ' ORDER BY Pst.CreatedDate DESC');

# To debug, uncomment the following line:
#SELECT @qry;

	PREPARE stmt FROM @qry;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;

	# log the search
	# check it's a genuine search and not just an admin page listing posts or listing a user's posts
	IF LogSearch IS NOT NULL AND LogSearch = 1 THEN
		INSERT INTO tbl_SearchQueries (SearchString, Wanted, Offered, CategoryIds, DateFrom, SearchDateTime)
		VALUES (SearchString, Wanted, Offered, CategoryIds, DateFrom, NOW());
	END IF;
END$$

