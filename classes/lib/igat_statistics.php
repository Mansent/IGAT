<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for calculating statistics and analytics from the database.
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
		global $DB, $CFG;
		$studentRoleId = 5;
		$sql = "SELECT (
					SELECT COUNT(*) FROM `" . $CFG->prefix . "badge_issued` WHERE badgeid = '$badgeId'
				) / (
					SELECT COUNT(*) FROM `" . $CFG->prefix . "user_enrolments` INNER JOIN `" . $CFG->prefix . "enrol` 
						ON " . $CFG->prefix . "user_enrolments.enrolid = " . $CFG->prefix . "enrol.id 
						WHERE `courseid` = '" . $this->courseId . "' AND `roleid` = '$studentRoleId' 
				) AS achievementrate"; 
		$db_record = $DB->get_record_sql($sql);
		$achievementRate = doubleval($db_record->achievementrate);
		
		if($achievementRate > 0) {
			$achievementRate *= 100; // return percentage
		}
		
		return round($achievementRate) . "%";
	}
  
  /**
   * Calculates the percentage of users in the same, a higher and a lower level for a user
	 * @param int $userId the id of the user the statistics should be calculated for
   * @retrun array the calculated statistics
   */
  public function getUserLevelStatistics($userId) {
    global $DB, $CFG;
    
    //Load level info
    $userInfo = $this->lib_progress->getUserInfo($userId);
    $userLevel = $userInfo->lvl;
    
    if($userLevel == "") {
      return null;
    }
    
    //Calculate statistic    
    $num_total = $DB->count_records_sql("SELECT COUNT(*) FROM `" . $CFG->prefix . "block_xp` WHERE courseid = $this->courseId");
    $num_lower = $DB->count_records_sql("SELECT COUNT(*) FROM `" . $CFG->prefix . "block_xp` WHERE `lvl` < $userLevel AND courseid = $this->courseId");
    $num_higher = $DB->count_records_sql("SELECT COUNT(*) FROM `" . $CFG->prefix . "block_xp` WHERE `lvl` > $userLevel AND courseid = $this->courseId");
    $num_equal = $DB->count_records_sql("SELECT COUNT(*) FROM `" . $CFG->prefix . "block_xp` WHERE `lvl` = $userLevel AND courseid = $this->courseId");
        
    if($num_total == 0) { // avoid division by zero
      return null;
    }
    
    $result->lower = round($num_lower / $num_total, 2);
    $result->higher = round($num_higher / $num_total, 2);
    $result->equal = round($num_equal / $num_total, 2);
    return $result;
  }
	
	/**
	 * By default, the log files for the gamification get deleted by the level up plugin after 3 days.
	 * We need these files for our analysis, so this function deactivates the deletion of gamification 
	 * log files by deactivating the scheduled task.
	 */
	public function disableGamificationLogDeletion() {
		global $DB, $CFG;
		$sql = "UPDATE " . $CFG->prefix . "task_scheduled SET disabled = 1 WHERE `component` = 'block_xp'";
		$DB->execute($sql);
	}
	
	/**
   * Calculates the number of page views for each tab in the gamification dashboard filtered by learning style
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   * @param int $minDate the earliest date to include in the statistic
   * @param int $maxDate the latest date to include in the statistic
   */
	public function getDashboardPageViews($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11, $minDate = "", $maxDate = "") { 
		global $DB, $CFG;
    
    // Get min and max date for labels
    $sql = "SELECT FROM_UNIXTIME(MIN(time)/1000) AS mindate, FROM_UNIXTIME(MAX(time)/1000) AS maxdate FROM `" . $CFG->prefix . "block_igat_dashboard_log`";
    $record = $DB->get_record_sql($sql);
    $recordsMinDate = strtotime($record->mindate);
    $recordsMaxDate = strtotime($record->maxdate); 
    
    if($minDate != "") {
      $recordsMinDate = strtotime($minDate);
    }
    if($maxDate != "") {
      $recordsMaxDate = strtotime($maxDate);
    }
    
    // Add date filter sql
    $dateSQL = "";
    if(!empty($minDate)) {
      $unixDate = strtotime($minDate) * 1000;
      $dateSQL = " AND time >= " . $unixDate . " ";
    }
    if(!empty($maxDate)) {
      $unixDate = (strtotime($maxDate) * 1000) + 86400000; // one day has 86400 milliseconds
      $dateSQL .= " AND time <= " . $unixDate . " ";
    }
    
		$result;
    $sql = "SELECT FROM_UNIXTIME(time/1000) AS date, COUNT(*) AS views FROM " . $CFG->prefix . "block_igat_dashboard_log 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_dashboard_log.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE tab = '+++tab+++' AND " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
                $dateSQL
              GROUP BY DAY(date) ORDER BY date";
              
		//progress tab
		$tabSql = str_replace('+++tab+++', 'progress', $sql);
		$records = $DB->get_records_sql($tabSql);	
		$progressRecords = $this->analyzeDashboardRecords($records);
		
		//badges tab
		$tabSql = str_replace('+++tab+++', 'badges', $sql);
		$records = $DB->get_records_sql($tabSql);		
		$badgesRecords = $this->analyzeDashboardRecords($records);
		
		//ranks tab
		$tabSql = str_replace('+++tab+++', 'ranks', $sql);
		$records = $DB->get_records_sql($tabSql);	
		$ranksRecords = $this->analyzeDashboardRecords($records);
		
		//settigs tab
		$tabSql = str_replace('+++tab+++', 'settings', $sql);
		$records = $DB->get_records_sql($tabSql);	
		$settingsRecords = $this->analyzeDashboardRecords($records);
		
    //generate labels for all days between min and max date
		$result->labels = array();
    $start = new DateTime(date('Y-m-d', $recordsMinDate));
    $end = new DateTime(date('Y-m-d', $recordsMaxDate));
    $end->setTime(0, 0, 1); // avoid excluding maxDate from loop
    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
    foreach ($period as $date) {
        array_push($result->labels, $date->format('d.m.'));
    }
    $result->progress = $this->generateContinousDataArray($period, $progressRecords);
    $result->badges = $this->generateContinousDataArray($period, $badgesRecords);
    $result->ranks = $this->generateContinousDataArray($period, $ranksRecords);
    $result->settings = $this->generateContinousDataArray($period, $settingsRecords);
		
		return $result;
	}
  
  /**
   * Calculates the average view durations for each tab in the gamification dashboard filtered by learning style
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   * @param int $minDate the earliest date to include in the statistic
   * @param int $maxDate the latest date to include in the statistic
   */
	public function getAverageDashboardViewDurations($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11, $minDate = "", $maxDate = "") { 
		global $DB, $CFG;        
		$result;
    
    // Add date filter sql
    $dateSQL = "";
    if(!empty($minDate)) {
      $unixDate = strtotime($minDate) * 1000;
      $dateSQL = " AND time >= " . $unixDate . " ";
    }
    if(!empty($maxDate)) {
      $unixDate = (strtotime($maxDate) * 1000) + 86400000; // one day has 86400 milliseconds
      $dateSQL .= " AND time <= " . $unixDate . " ";
    }
    
    $sql = "SELECT AVG(duration) AS average FROM " . $CFG->prefix . "block_igat_dashboard_log 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_dashboard_log.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE tab = '+++tab+++' AND " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
                $dateSQL";

		//progress tab
		$tabSql = str_replace('+++tab+++', 'progress', $sql);
		$record = $DB->get_record_sql($tabSql);	
    $result->progress = (int)($record->average / 1000);
		
		//badges tab
		$tabSql = str_replace('+++tab+++', 'badges', $sql);
		$record = $DB->get_record_sql($tabSql);	
    $result->badges = (int)($record->average / 1000);
		
		//ranks tab
		$tabSql = str_replace('+++tab+++', 'ranks', $sql);
		$record = $DB->get_record_sql($tabSql);	
    $result->ranks = (int)($record->average / 1000);
		
		//settigs tab
		$tabSql = str_replace('+++tab+++', 'settings', $sql);
		$record = $DB->get_record_sql($tabSql);	
    $result->settings = (int)($record->average / 1000);
    
		return $result;
	}
  
  /**
   * Counts which visibility setting was chosen by how many students filtered by learning syle
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */
	public function getVisabilitySettingsStatistics($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) { 
		global $DB, $CFG;        
		$result;
    
    $sql = "SELECT COUNT(*) as sum FROM `" . $CFG->prefix . "block_igat_usersettings` 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_usersettings.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_usersettings.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE leaderboarddisplay = '+++display+++' AND " . $CFG->prefix . "block_igat_usersettings.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax";

		//hidden display
		$displaySql = str_replace('+++display+++', 'hide', $sql);
		$record = $DB->get_record_sql($displaySql);	
    $result->hide = $record->sum;
		
		//limited display
		$displaySql = str_replace('+++display+++', 'limited', $sql);
		$record = $DB->get_record_sql($displaySql);	
    $result->limited = $record->sum;
		
		//display all
		$displaySql = str_replace('+++display+++', 'all', $sql);
		$record = $DB->get_record_sql($displaySql);	
    $result->all = $record->sum;
		
		return $result;
	}

 /**
   * Counts which anonymity setting was chosen by how many students filtered by learning syle
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */
	public function getAnonymitySettingsStatistics($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) { 
		global $DB, $CFG;        
		$result;
    
    $sql = "SELECT COUNT(*) as sum FROM `" . $CFG->prefix . "block_igat_usersettings` 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_usersettings.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_usersettings.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE anonymousleaderboard = '+++anonymity+++' AND " . $CFG->prefix . "block_igat_usersettings.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax";

		//hidden display
		$anonymitySql = str_replace('+++anonymity+++', '1', $sql);
		$record = $DB->get_record_sql($anonymitySql);	
    $result->hide = $record->sum;
		
		//limited display
		$anonymitySql = str_replace('+++anonymity+++', '0', $sql);
		$record = $DB->get_record_sql($anonymitySql);	
    $result->show = $record->sum;
		
		return $result;
	}

 /**
   * Gets the statistics for the subsequent pages in the gamification dashboard
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   * @param int $minDate the earliest date to include in the statistic
   * @param int $maxDate the latest date to include in the statistic
   */
	public function getSubsequentPagesStatistics($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11, $minDate = "", $maxDate = "") { 
		global $DB, $CFG;        
		$result;
    
    // Add date filter sql
    $dateSQL = "";
    if(!empty($minDate)) {
      $unixDate = strtotime($minDate) * 1000;
      $dateSQL = " AND time >= " . $unixDate . " ";
    }
    if(!empty($maxDate)) {
      $unixDate = (strtotime($maxDate) * 1000) + 86400000; // one day has 86400 milliseconds
      $dateSQL .= " AND time <= " . $unixDate . " ";
    }
    
    $sql = "SELECT " . $CFG->prefix . "block_igat_dashboard_log.id, tab, next_page, COUNT(*) AS sum FROM `" . $CFG->prefix . "block_igat_dashboard_log` 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_dashboard_log.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE tab != next_page AND " . $CFG->prefix . "block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
                $dateSQL
							GROUP BY tab, next_page";

		//hidden display
		$records = $DB->get_records_sql($sql);
		
		$total = array();
		$edges = array();
		foreach($records as &$record) {
			if(!isset($total[$record->tab])) {
				$total[$record->tab] = 0;
			}
			$total[$record->tab] += $record->sum;
			$edges[$record->tab][$record->next_page] = $record->sum;
		}
		
		$tabs = array('progress', 'badges', 'ranks', 'settings', 'moodle', 'external');
		foreach($tabs as &$from) {
			foreach($tabs as &$to) {
				if($from != 'moodle' && $from != 'external') {
					if(!isset($edges[$from][$to])) {
						$edges[$from][$to] = 0;
					}
					else {
						$percentage = round($edges[$from][$to] / $total[$from] * 100);
						$edges[$from][$to] = (int)$percentage;
					}
				}
			}
		}
		
		return $edges;
	}

 /**
   * Gets the gamification feedback rate (average number of positive reinforcements 
	 * of the gamification per day, reinforcements e.g. user earning points/badges/leveling up)
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   * @param int $minDate the earliest date to include in the statistic
   * @param int $maxDate the latest date to include in the statistic
   */	
	public function getGamificationFeedbackRate($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11, $minDate = "", $maxDate = "") {
		global $DB, $CFG;
		// Add date filter sql
    $dateSQL = "";
    if(!empty($minDate)) {
      $unixDate = strtotime($minDate);
      $dateSQL = " AND timecreated >= " . $unixDate . " ";
    }
    if(!empty($maxDate)) {
      $unixDate = strtotime($maxDate) + 86400; // one day has 86400 milliseconds
      $dateSQL .= " AND timecreated <= " . $unixDate . " ";
    }
		
    /* 1. Get days each student active in course
     * 2. Join count of gamification events to this
     * 3. Join learning styles filter to the result
     * 4. Take average event counts as feedback rate */
    if(!$this->lib_progress->hasLevelUpPlus()) { // the log table layout is different for level up and level up plus
      $levelup_log_join = "LEFT JOIN " . $CFG->prefix . "block_xp_log 
                            ON " . $CFG->prefix . "block_xp_log.userid = l.userid 
                              AND " . $CFG->prefix . "block_xp_log.courseid = l.courseid 
                              AND DATE(FROM_UNIXTIME(" . $CFG->prefix . "block_xp_log.time)) = l.d ";
    }
    else {
      $levelup_log_join = "LEFT JOIN (
                            SELECT userid, instanceid AS courseid, time 
                            FROM " . $CFG->prefix . "local_xp_log 
                            INNER JOIN mdl_context 
                            ON " . $CFG->prefix . "local_xp_log.contextid = " . $CFG->prefix . "context.id  
                           ) AS logtable
                           ON logtable.userid = l.userid 
                            AND logtable.courseid = l.courseid 
                            AND DATE(FROM_UNIXTIME(logtable.time)) = l.d ";
    }
    $sql = "SELECT id, AVG(Eventcount.sum) AS feedbackRate FROM (
              SELECT c.id, c.d, c.sum FROM (
                SELECT l.id, l.userid, l.courseid, l.d, COUNT(time) AS sum  FROM (
                  SELECT id, courseid, userid, DATE(FROM_UNIXTIME(timecreated)) AS d FROM `" . $CFG->prefix . "logstore_standard_log` 
                  WHERE action = 'viewed' AND target = 'course' AND courseid = " . $this->courseId . " $dateSQL
                  GROUP BY userid, d
                ) AS l 
                $levelup_log_join
                GROUP BY l.userid, l.d
              ) AS c 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON c.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND c.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE c.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
            ) AS Eventcount";
		$record = $DB->get_record_sql($sql);
		if(empty($record->feedbackrate)) {
			return 0;
		}
		return (float)$record->feedbackrate;
	}
	
	/**
   * Gets the point distribution of the current students in this course
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */	
	public function getPointsDistribution($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
		global $DB, $CFG;
		
		//Get bins size in relation to number of levels and points for highest level
		$levelsInfo = $this->lib_progress->getLevelsInfo();
		$numLevels = count($levelsInfo);
		$maxLevelPoints = $levelsInfo[$numLevels];
		$binSizeUnrounded = (int)($maxLevelPoints / (1.2 * $numLevels));
		$binSize = (int)round($binSizeUnrounded, -(strlen($binSizeUnrounded) - 1));
		$bins = array();
		$currentBin = 0;
		while($currentBin < $maxLevelPoints) {
			array_push($bins, $currentBin);
			$currentBin += $binSize;
		}
		
		// Build sql query for bins
		$binSql = "";
		for($i=0; $i<count($bins); $i++) {
			if($i < (count($bins) - 1)) {
				$binSql .= "WHEN xp >= " . $bins[$i] . " AND xp < " . $bins[$i+1] . " THEN '[" . $bins[$i] . ", " . $bins[$i+1]. "]' ";
			}
			else {
				$binSql .= "ELSE '>=" . $bins[$i] . "'";
			}
		}
		$sql = "SELECT " . $CFG->prefix . "block_xp.id, COUNT(*) AS sum, CASE " . $binSql . "	END AS bins
						FROM `" . $CFG->prefix . "block_xp` 
						INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_xp.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_xp.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE " . $CFG->prefix . "block_xp.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
							GROUP BY bins"; 
		$histogram = array();
		$records = $DB->get_records_sql($sql);
		foreach($records as &$record) {
			$histogram[$record->bins] = $record->sum;
		}
		
		// Add bins with 0 students to histogram
		$result = array();
		for($i=0; $i<count($bins); $i++) {
			if($i < (count($bins) - 1)) {
				$key = "[" . $bins[$i] . ", " . $bins[$i+1]. "]";
			}
			else {
				$key = ">=" . $bins[$i];
			}
			if(!isset($histogram[$key])) {
				$result[$key] = 0;
			}
			else {
				$result[$key] = $histogram[$key];
			}
		}
		return $result;
	}

	/**
   * Gets the levels distribution of the current students in this course
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */	
	public function getLevelsDistribution($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
		global $DB, $CFG;
		
		$levelsInfo = $this->lib_progress->getLevelsInfo();
		$sql = "SELECT lvl, COUNT(*) AS sum FROM `" . $CFG->prefix . "block_xp` 
						INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_xp.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_xp.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE " . $CFG->prefix . "block_xp.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
						GROUP BY lvl"; 
		
		$data = array();
		$records = $DB->get_records_sql($sql);
		foreach($records as &$record) {
			$data[$record->lvl] = $record->sum;
		}
		
		// Add levels with 0 students to result
		$result = array();
		for($i=0; $i<count($levelsInfo); $i++) {
			if(!isset($data[$i])) {
				$result[$i] = 0;
			}
			else {
				$result[$i] = $data[$i];
			}
		}
		return $result;
	}
  
  /**
   * Gets the badges distribution of the current students in this course
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */	
  public function getBadgesDistribution($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
    global $DB, $CFG;
    $sql = "SELECT name, COUNT(badgeid) AS sum FROM " . $CFG->prefix . "badge 
            LEFT JOIN ( 
              SELECT badgeid FROM " . $CFG->prefix . "badge_issued 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles 
                ON " . $CFG->prefix . "badge_issued.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
            ) AS issu 
            ON " . $CFG->prefix . "badge.id = issu.badgeid
            WHERE courseid = " . $this->courseId . "
            GROUP BY " . $CFG->prefix . "badge.id ";
    
    $data = array();
		$records = $DB->get_records_sql($sql);
		foreach($records as &$record) {
			$data[$record->name] = $record->sum;
		}
    return $data;
  }
  
  /**
   * Gets the average days needed to advance to all levels filtered by learning style
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */	
  public function getAverageDaysToLevel($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
    global $DB, $CFG;
    // Get average days between first gamification event and level up time for each level, a day has 86400 seconds
    $sql = "SELECT id, newlevel, AVG((leveluptime - firsteventtime) / 86400) AS avgdays FROM (
              SELECT " . $CFG->prefix . "block_igat_levelup_log.id,
                     " . $CFG->prefix . "block_igat_levelup_log.courseid, 
                     " . $CFG->prefix . "block_igat_levelup_log.userid, 
                     newlevel, 
                     " . $CFG->prefix . "block_igat_levelup_log.time as leveluptime, 
                     moodlelog.timecreated as firsteventtime 
              FROM `" . $CFG->prefix . "block_igat_levelup_log`
              INNER JOIN (
                  SELECT * FROM `" . $CFG->prefix . "logstore_standard_log` 
                  	WHERE action = 'viewed' AND target = 'course' AND courseid = " . $this->courseId . " 
                  	GROUP BY userid
              ) AS moodlelog
                  ON " . $CFG->prefix . "block_igat_levelup_log.courseid = moodlelog.courseid 
                  AND " . $CFG->prefix . "block_igat_levelup_log.userid = moodlelog.userid 
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "block_igat_levelup_log.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "block_igat_levelup_log.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE " . $CFG->prefix . "block_igat_levelup_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
              GROUP BY " . $CFG->prefix . "block_igat_levelup_log.courseid, " . $CFG->prefix . "block_igat_levelup_log.userid, " . $CFG->prefix . "block_igat_levelup_log.newlevel
            ) AS levelups 
            GROUP BY newlevel";
    $records = $DB->get_records_sql($sql);
    $data = array();
    foreach($records as &$record) {
      $data[$record->newlevel] = $record->avgdays;
    }
    
    //fill in missing level info 
		$levelsInfo = $this->lib_progress->getLevelsInfo();
    $numLevels = count($levelsInfo);
    $result = array();
    for($level=1; $level<=$numLevels; $level++) {
      if(empty($data[$level])) {
        $result[$level] = 0;
      }
      else {
        $result[$level] = (float)$data[$level];
      }
    }
    return $result;
  }
 
  /**
   * Gets the average days needed to earn a badge for all badges filtered by learning style
   * @param int $processingMin the minimum processing learning style score
   * @param int $processingMax the maximum processing learning style score
   * @param int $perceptionMin the minimum perception learning style score
   * @param int $perceptionMax the maximum perception learning style score
   * @param int $inputMin the minimum input learning style score
   * @param int $inputMax the maximum input learning style score
   * @param int $comprehensionMin the minimum comprehension learning style score
   * @param int $comprehensionMax the maximum comprehension learning style score
   */	 
  public function getAverageDaysToBadges($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
    global $DB, $CFG;
    $sql = "SELECT id, name, userid, AVG((dateissued - firsteventtime) / 86400) AS avgdays FROM ( 
              SELECT " . $CFG->prefix . "badge.id, 
                     name, 
                     " . $CFG->prefix . "badge_issued.userid, 
                     dateissued, 
                     moodlelog.timecreated as firsteventtime 
              FROM `" . $CFG->prefix . "badge` 
              INNER JOIN `" . $CFG->prefix . "badge_issued` ON " . $CFG->prefix . "badge.id = " . $CFG->prefix . "badge_issued.badgeid 
              INNER JOIN (
                  SELECT * FROM `" . $CFG->prefix . "logstore_standard_log` 
                  	WHERE action = 'viewed' AND target = 'course' AND courseid = " . $this->courseId . " 
                  	GROUP BY userid
              ) AS moodlelog
                ON " . $CFG->prefix . "badge.courseid = moodlelog.courseid 
                  AND " . $CFG->prefix . "badge_issued.userid = moodlelog.userid  
              INNER JOIN " . $CFG->prefix . "block_igat_learningstyles ON 
                " . $CFG->prefix . "badge.courseid = " . $CFG->prefix . "block_igat_learningstyles.courseid 
                AND " . $CFG->prefix . "badge_issued.userid = " . $CFG->prefix . "block_igat_learningstyles.userid 
              WHERE " . $CFG->prefix . "badge.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
              GROUP BY " . $CFG->prefix . "badge.id, " . $CFG->prefix . "badge.courseid, " . $CFG->prefix . "badge_issued.userid
            ) AS d 
            GROUP BY id";
    $records = $DB->get_records_sql($sql);
    $data = array();
    foreach($records as &$record) {
      $data[$record->name] = $record->avgdays;
    }
    return $data;
  }

  /**
   * Helper function that builds an array of the data filling in the missing dates from a period
   * $period DatePeriod the period to fill in missing dates
   * $data the data to use
   * @returns array of the data with zero for the missing dates in the period
   */   
  private function generateContinousDataArray($period, $data) {
    $result = array();
    foreach ($period as $date) {
      $dm = $date->format('d.m.');
      if(array_key_exists($dm, $data)) {
        array_push($result, $data[$dm]);
      }
      else {
        array_push($result, 0);
      }
    }
    return $result;
  }
	
  /**
   * Helper function that processes database records to data array 
   * $records the records from an sql query
   * @return the processed data array
   */
  private function analyzeDashboardRecords($records) {
    $data = array();
    foreach($records as &$record) {
      $date = strtotime($record->date);
      $dateFormatted = date( 'd.m.', $date) ;
      $data[$dateFormatted] = $record->views;
    }
    return $data;
  }
}
?>