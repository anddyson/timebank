delimiter $$

CREATE DEFINER=`root`@`localhost` PROCEDURE `spc_UpdateTransaction`(
	Id INTEGER
	,GiverId INTEGER
	,ReceiverId INTEGER
	,PostId INTEGER
	,Hours INTEGER
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
	UPDATE tbl_Transactions Trn
		SET Trn.GiverId = GiverId
		,Trn.ReceiverId = ReceiverId
		,Trn.PostId = PostId
		,Trn.Hours = Hours
		,Trn.Description = Description
		,Trn.GiverApproved = GiverApproved
		,Trn.ReceiverApproved = ReceiverApproved
		,Trn.GiverApprovedDateTime = GiverApprovedDateTime
		,Trn.ReceiverApprovedDateTime = ReceiverApprovedDateTime
		,Trn.TransactionDateTime = TransactionDateTime
		,Trn.IsDisputed = IsDisputed
		,Trn.DisputeRaisedDateTime = DisputeRaisedDateTime
		,Trn.DisputeRaisedById = DisputeRaisedById
		,Trn.DisputeResolvedDateTime = DisputeResolvedDateTime
		,Trn.DisputeResolvedById = DisputeResolvedById
		,Trn.DisputeNotes = DisputeNotes
	WHERE Trn.Id = Id;
END$$

