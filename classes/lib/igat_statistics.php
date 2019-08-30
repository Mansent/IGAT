<?php
/**
 * Library for calculating various statistics from the database.
 */
class igat_statistics 
{
  private $courseId;
  
  private $lib_progress;
  
  /**
   * Creates a new igat statistics object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
    $this->lib_progress = new igat_progress($courseId);
		$this->disableGamificationLogDeletion();
  }
	
	/**
	 * Calculates the percentage of students in a course who have earned a badge.
	 * @param int $badgeId the id of the badge to refer to 
	 * @param int $courseId the id of the course to refer to
	 * @return the calculated badge achievement rate
	 */
	public function getBadgeAchievementRate($badgeId) {
		global $DB;
		$studentRoleId = 5;
		$sql = "SELECT (
					SELECT COUNT(*) FROM `mdl_badge_issued` WHERE badgeid = '$badgeId'
				) / (
					SELECT COUNT(*) FROM `mdl_enrol` WHERE `courseid` = '" . $this->courseId . "' AND `roleid` = '$studentRoleId' 
				) AS achievementrate";
		$db_record = $DB->get_record_sql($sql);
		$achievementRate = doubleval($db_record->achievementrate);
		
		if($achievementRate > 0) {
			$achievementRate *= 100; // return percentage
		}
		
		return $achievementRate . "%";
	}
  
  /**
   * Calculates the percentage of users in the same, a higher and a lower level for a user
	 * @param int $userId the id of the user the statistics should be calculated for
   * @retrun array the calculated statistics
   */
  public function getUserLevelStatistics($userId) {
    global $DB;
    
    //Load level info
    $userInfo = $this->lib_progress->getUserInfo($userId);
    $userLevel = $userInfo->lvl;
    
    if($userLevel == "") {
      return null;
    }
    
    //Calculate statistic    
    $num_total = $DB->count_records_sql("SELECT COUNT(*) FROM `mdl_block_xp` WHERE courseid = $this->courseId");
    $num_lower = $DB->count_records_sql("SELECT COUNT(*) FROM `mdl_block_xp` WHERE `lvl` < $userLevel AND courseid = $this->courseId");
    $num_higher = $DB->count_records_sql("SELECT COUNT(*) FROM `mdl_block_xp` WHERE `lvl` > $userLevel AND courseid = $this->courseId");
    $num_equal = $DB->count_records_sql("SELECT COUNT(*) FROM `mdl_block_xp` WHERE `lvl` = $userLevel AND courseid = $this->courseId");
    
    if($num_total == 0) { // avoid division by zero
      return null;
    }
    
    $result->lower = $num_lower / $num_total;
    $result->higher = $num_higher / $num_total;
    $result->equal = $num_equal / $num_total;
    return $result;
  }
	
	/**
	 * By default, the log files for the gamification get deleted by the level up plugin after 3 days.
	 * We need these files for our analysis, so this function deactivates the deletion of gamification 
	 * log files by deactivating the scheduled task.
	 */
	public function disableGamificationLogDeletion() {
		global $DB;
		$sql = "UPDATE mdl_task_scheduled SET disabled = 1 WHERE `component` = 'block_xp'";
		$DB->execute($sql);
	}
	
	
	public function getDashboardPageViews() { 
		global $DB;
		$result;
		$sql = "SELECT FROM_UNIXTIME(time/1000) AS date, COUNT(*) AS views FROM mdl_block_igat_dashboard_log ";
		$groupby = " GROUP BY DAY(date) ORDER BY date";

		//progress tab
		$where = "WHERE tab = 'progress' AND courseid = " . $this->courseId . " ";
		$records = $DB->get_records_sql($sql . $where . $groupby);	
		$result->progress = $this->analyzeDashboardRecords($records);
		
		//badges tab
		$where = "WHERE tab = 'badges' AND courseid = " . $this->courseId . " ";
		$records = $DB->get_records_sql($sql . $where . $groupby);	
		$result->badges = $this->analyzeDashboardRecords($records);
		
		//ranks tab
		$where = "WHERE tab = 'ranks' AND courseid = " . $this->courseId . " ";
		$records = $DB->get_records_sql($sql . $where . $groupby);	
		$result->ranks = $this->analyzeDashboardRecords($records);
		
		//settigs tab
		$where = "WHERE tab = 'settings' AND courseid = " . $this->courseId . " ";
		$records = $DB->get_records_sql($sql . $where . $groupby);	
		$result->settings = $this->analyzeDashboardRecords($records);
		
		$result->labels = $progress->labels; // TODO find correct labels
		
		return $result;
	}
	
	private function analyzeDashboardRecords($records) {
		$result = new stdClass();
		$labels = array();
		$data = array();
		foreach($records as &$record) {
				$date = strtotime($record->date);
				
				array_push($labels, date( 'd.m.', $date));
				array_push($data, $record->views);
		}
		$result->labels = $labels;
		$result->data = $data;
		return $result;
	}
}
?>