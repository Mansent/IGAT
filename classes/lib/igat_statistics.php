<?php
/**
 * Library for calculating various statistics from the database.
 */
class igat_statistics {
	
	/**
	 * Calculates the percentage of students in a course who have earned a badge.
	 * @param int $badgeId the id of the badge to refer to 
	 * @param int $courseId the id of the course to refer to
	 * @return the calculated badge achievement rate
	 */
	public function getBadgeAchievementRate($badgeId, $courseId) {
		global $DB;
		$studentRoleId = 5;
		$sql = "SELECT (
					SELECT COUNT(*) FROM `mdl_badge_issued` WHERE badgeid = '$badgeId'
				) / (
					SELECT COUNT(*) FROM `mdl_enrol` WHERE `courseid` = '$courseId' AND `roleid` = '$studentRoleId' 
				) AS achievementrate";
		$db_record = $DB->get_record_sql($sql);
		$achievementRate = doubleval($db_record->achievementrate);
		
		if($achievementRate > 0) {
			$achievementRate *= 100; // return percentage
		}
		
		return $achievementRate . "%";
	}
}
?>