delimiter $$

CREATE DEFINER=`root`@`%` PROCEDURE `spc_FinishJob`(
	JobId INT
)
BEGIN

	# work out when the job should next be run, based on its interval settings
	#SET @NextRun = DATE_ADD(@LastRun, INTERVAL @Interval @IntervalType);
	SELECT
		CASE IntervalType
			WHEN "MINUTE" THEN date_add(Job.LastRun, INTERVAL Job.RunInterval MINUTE)
			WHEN "HOUR" THEN date_add(Job.LastRun, INTERVAL Job.RunInterval HOUR)
			WHEN "DAY" THEN date_add(Job.LastRun, INTERVAL Job.RunInterval DAY)
			WHEN "WEEK" THEN date_add(Job.LastRun, INTERVAL Job.RunInterval WEEK)
			WHEN "MONTH" THEN date_add(Job.LastRun, INTERVAL Job.RunInterval MONTH)
       END
       AS NextRun INTO @NextRun
	FROM tbl_AutomatorJobs Job
	WHERE Job.Id = JobId;

	UPDATE tbl_AutomatorJobs Job
	SET Job.Running = False, Job.NextRun = @NextRun
	WHERE Job.Id = JobId;
END$$

