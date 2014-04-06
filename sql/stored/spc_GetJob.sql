delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_GetJob`(
	JobId INT
)
BEGIN

IF JobId IS NOT NULL THEN
	SELECT Job.Id, Job.Description, Job.RunInterval, Job.RunIntervalType, Job.Running, Job.LastRun, Job.NextRun
	INTO @Id, @Description, @RunInterval, @RunIntervalType, @Running, @LastRun, @NextRun
	FROM tbl_AutomatorJobs Job
	WHERE Job.Id = JobId;
ELSE
	SELECT Job.Id, Job.Description, Job.RunInterval, Job.RunIntervalType, Job.Running, Job.LastRun, Job.NextRun
	INTO @Id, @Description, @RunInterval, @RunIntervalType, @Running, @LastRun, @NextRun
	FROM tbl_AutomatorJobs Job
	WHERE Job.Running = False
	AND Job.NextRun < NOW()
	ORDER BY Job.NextRun ASC
	LIMIT 1;
END IF;

IF @Id > 0 THEN
	UPDATE tbl_AutomatorJobs Job
	SET Job.Running = 1, Job.LastRun = NOW()
	WHERE Job.Id = @Id;

	SELECT
		@Id AS Id
		,@Description AS Description
		,@RunInterval As RunInterval
		,@RunIntervalType As RunIntervalType
		,@Running As Running
		,@LastRun As LastRun
		,@NextRun As NextRun;
END IF;

END$$

