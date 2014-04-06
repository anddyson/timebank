delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_ConfirmTransaction`(
	TrnId INT
	,UserId INT
	,Uuid CHAR(36)
)
BEGIN

IF TrnId IS NOT NULL AND UserId IS NOT NULL THEN
	# It'll either be this one...
	UPDATE tbl_Transactions Trn
	SET Trn.GiverApproved = 1, Trn.GiverApprovedDateTime = NOW()
	WHERE Trn.Id = TrnId AND Trn.GiverId = UserId AND Trn.GiverApproved = 0;

	# ...or this one
	UPDATE tbl_Transactions Trn
	SET Trn.ReceiverApproved = 1, Trn.ReceiverApprovedDateTime = NOW()
	WHERE Trn.Id = TrnId AND Trn.ReceiverId = UserId  AND Trn.ReceiverApproved = 0;

ELSEIF Uuid IS NOT NULL THEN

	# It'll either be this one
	UPDATE tbl_Transactions Trn
	SET Trn.GiverApproved = 1, Trn.GiverApprovedDateTime = NOW()
	WHERE Trn.Uuid = Uuid AND Trn.GiverApproved = 0;

	# ... or this one
	UPDATE tbl_Transactions Trn
	SET Trn.ReceiverApproved = 1, Trn.ReceiverApprovedDateTime = NOW()
	WHERE Trn.Uuid = Uuid AND Trn.ReceiverApproved = 0;
END IF;
END$$

