delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_DeleteTransaction`(
	TrnId INTEGER
)
BEGIN
	DELETE FROM tbl_Transactions
	WHERE Id = TrnId;
END$$

