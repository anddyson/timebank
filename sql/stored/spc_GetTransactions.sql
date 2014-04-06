delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_GetTransactions`(
	TrnId INT
	,UserId INT
)
BEGIN
	SELECT *
	FROM tbl_Transactions Trn
	WHERE
		(Trn.Id = TrnId OR TrnId IS NULL)
		AND (Trn.GiverId = UserId OR Trn.ReceiverId = UserId OR UserId IS NULL)
	ORDER BY Trn.TransactionDateTime DESC;
END$$

