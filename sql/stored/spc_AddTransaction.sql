delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_AddTransaction`(
	GiverId INTEGER
	,ReceiverId INTEGER
	,PostId INTEGER
	,Hours FLOAT
	,Description VARCHAR(500)
	,GiverApproved BOOLEAN
	,ReceiverApproved BOOLEAN
	,GiverApprovedDateTime DATETIME
	,ReceiverApprovedDateTime DATETIME
	,TransactionDateTime DATETIME
	,IsDisputed BOOLEAN
	,DisputeRaisedDateTime DATETIME
	,DisputeRaisedById INTEGER
	,DisputeResolvedDateTime DATETIME
	,DisputeResolvedById INTEGER
	,DisputeNotes VARCHAR(4000)
)
BEGIN
	INSERT INTO tbl_Transactions
	(
		GiverId
		,ReceiverId
		,PostId
		,Hours
		,Description
		,GiverApproved
		,ReceiverApproved
		,GiverApprovedDateTime
		,ReceiverApprovedDateTime
		,TransactionDateTime
		,IsDisputed
		,DisputeRaisedDateTime
		,DisputeRaisedById
		,DisputeResolvedDateTime
		,DisputeResolvedById
		,DisputeNotes
		,Uuid
	)
	VALUES
	(
		GiverId
		,ReceiverId
		,PostId
		,Hours
		,Description
		,GiverApproved
		,ReceiverApproved
		,GiverApprovedDateTime
		,ReceiverApprovedDateTime
		,TransactionDateTime
		,IsDisputed
		,DisputeRaisedDateTime
		,DisputeRaisedById
		,DisputeResolvedDateTime
		,DisputeResolvedById
		,DisputeNotes
		,UUID()
	);

	# return the transaction with all details, including the UUID, completed
	SELECT * FROM tbl_Transactions
	WHERE Id = last_insert_id();
END$$

