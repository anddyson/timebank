delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetGeneralStats`(
	DateFrom DATETIME
	,DateTo DATETIME
)
BEGIN

IF DateTo IS NULL THEN SET DateTo = NOW(); END IF;

SELECT SUM(Hours) As Hours INTO @Hours
FROM tbl_Transactions
WHERE GiverApproved = 1 AND ReceiverApproved = 1 AND IsDisputed = 0
AND (
	(GiverApprovedDateTime > ReceiverApprovedDateTime AND GiverApprovedDateTime >= DateFrom AND GiverApprovedDateTime < DateTo)
	OR (ReceiverApprovedDateTime > GiverApprovedDateTime AND ReceiverApprovedDateTime >= DateFrom AND ReceiverApprovedDateTime < DateTo)
)
;
IF @Hours IS NULL THEN SET @Hours = 0; END IF;

SELECT COUNT(Id) INTO @WantedCount FROM tbl_Posts Pst
WHERE Pst.Type = 1
AND Pst.ExpiryDate >= DateFrom
AND Pst.CreatedDate >= DateFrom
AND Pst.CreatedDate < DateTo;

SELECT COUNT(Id) INTO @OfferedCount FROM tbl_Posts Pst
WHERE Pst.Type = 2
AND Pst.ExpiryDate >= DateFrom
AND Pst.CreatedDate >= DateFrom
AND Pst.CreatedDate < DateTo;

SELECT Count(Id) INTO @MessagesSent FROM tbl_Messages Msg
WHERE Msg.SentDateTime >= DateFrom
AND Msg.SentDateTime <= DateTo;

SELECT
	ROUND(@Hours, 1) As Hours
	,@WantedCount As WantedCount
	,@OfferedCount AS OfferedCount
	,@MessagesSent As MessagesSent
;
END$$

