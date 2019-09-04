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
   */
	public function getDashboardPageViews($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) { 
		global $DB;
    
    // Get min and max date
    $sql = "SELECT FROM_UNIXTIME(MIN(time)/1000) AS mindate, FROM_UNIXTIME(MAX(time)/1000) AS maxdate FROM `mdl_block_igat_dashboard_log`";
    $record = $DB->get_record_sql($sql);
    $minDate = strtotime($record->mindate);
    $maxDate = strtotime($record->maxdate); 
        
		$result;
    $sql = "SELECT FROM_UNIXTIME(time/1000) AS date, COUNT(*) AS views FROM mdl_block_igat_dashboard_log 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_dashboard_log.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_dashboard_log.userid = mdl_block_igat_learningstyles.userid 
              WHERE tab = '+++tab+++' AND mdl_block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
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
    $start = new DateTime(date('Y-m-d', $minDate));
    $end = new DateTime(date('Y-m-d', $maxDate));
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
   */
	public function getAverageDashboardViewDurations($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) { 
		global $DB;        
		$result;
    
    $sql = "SELECT AVG(duration) AS average FROM mdl_block_igat_dashboard_log 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_dashboard_log.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_dashboard_log.userid = mdl_block_igat_learningstyles.userid 
              WHERE tab = '+++tab+++' AND mdl_block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax";

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
		global $DB;        
		$result;
    
    $sql = "SELECT COUNT(*) as sum FROM `mdl_block_igat_usersettings` 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_usersettings.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_usersettings.userid = mdl_block_igat_learningstyles.userid 
              WHERE leaderboarddisplay = '+++display+++' AND mdl_block_igat_usersettings.courseid = " . $this->courseId . " 
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
		global $DB;        
		$result;
    
    $sql = "SELECT COUNT(*) as sum FROM `mdl_block_igat_usersettings` 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_usersettings.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_usersettings.userid = mdl_block_igat_learningstyles.userid 
              WHERE anonymousleaderboard = '+++anonymity+++' AND mdl_block_igat_usersettings.courseid = " . $this->courseId . " 
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
   */
	public function getSubsequentPagesStatistics($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) { 
		global $DB;        
		$result;
    
    $sql = "SELECT mdl_block_igat_dashboard_log.id, tab, next_page, COUNT(*) AS sum FROM `mdl_block_igat_dashboard_log` 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_dashboard_log.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_dashboard_log.userid = mdl_block_igat_learningstyles.userid 
              WHERE tab != next_page AND mdl_block_igat_dashboard_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
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
   */	
	public function getGamificationFeedbackRate($processingMin = -11, $processingMax = 11, $perceptionMin = -11, $perceptionMax = 11, 
    $inputMin = -11, $inputMax = 11, $comprehensionMin = -11, $comprehensionMax = 11) {
		global $DB;
		$sql = "SELECT id, AVG(Eventcount.sum) AS feedbackRate FROM 
							(SELECT mdl_block_xp_log.id, mdl_block_xp_log.userid, COUNT(*) AS sum, DATE(FROM_UNIXTIME(time)) AS d FROM `mdl_block_xp_log`
							INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_xp_log.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_xp_log.userid = mdl_block_igat_learningstyles.userid 
              WHERE mdl_block_xp_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax
							GROUP BY userid, d) 
							AS Eventcount"; 
							
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
		global $DB;
		
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
		$sql = "SELECT mdl_block_xp.id, COUNT(*) AS sum, CASE " . $binSql . "	END AS bins
						FROM `mdl_block_xp` 
						INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_xp.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_xp.userid = mdl_block_igat_learningstyles.userid 
              WHERE mdl_block_xp.courseid = " . $this->courseId . " 
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
		global $DB;
		
		$levelsInfo = $this->lib_progress->getLevelsInfo();
		$sql = "SELECT lvl, COUNT(*) AS sum FROM `mdl_block_xp` 
						INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_xp.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_xp.userid = mdl_block_igat_learningstyles.userid 
              WHERE mdl_block_xp.courseid = " . $this->courseId . " 
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
    global $DB;
    // Get average days between first gamification event and level up time for each level, a day has 86400 seconds
    $sql = "SELECT id, newlevel, AVG((leveluptime - firsteventtime) / 86400) AS avgdays FROM (
              SELECT mdl_block_igat_levelup_log.id,
                     mdl_block_igat_levelup_log.courseid, 
                     mdl_block_igat_levelup_log.userid, 
                     newlevel, 
                     mdl_block_igat_levelup_log.time as leveluptime, 
                     mdl_block_xp_log.time as firsteventtime 
              FROM `mdl_block_igat_levelup_log` 
              INNER JOIN `mdl_block_xp_log`
                ON mdl_block_igat_levelup_log.courseid = mdl_block_xp_log.courseid 
                AND mdl_block_igat_levelup_log.userid = mdl_block_xp_log.userid 
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_block_igat_levelup_log.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_block_igat_levelup_log.userid = mdl_block_igat_learningstyles.userid 
              WHERE mdl_block_igat_levelup_log.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
              GROUP BY mdl_block_igat_levelup_log.courseid, mdl_block_igat_levelup_log.userid, mdl_block_igat_levelup_log.newlevel
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
    global $DB;
    $sql = "SELECT id, name, userid, AVG((dateissued - firsteventtime) / 86400) AS avgdays FROM ( 
              SELECT mdl_badge.id, 
                     name, 
                     mdl_badge_issued.userid, 
                     dateissued, 
                     mdl_block_xp_log.time as firsteventtime 
              FROM `mdl_badge` 
              INNER JOIN `mdl_badge_issued` ON mdl_badge.id = mdl_badge_issued.badgeid 
              INNER JOIN `mdl_block_xp_log` ON mdl_badge.courseid = mdl_block_xp_log.courseid 
                      AND mdl_badge_issued.userid = mdl_block_xp_log.userid  
              INNER JOIN mdl_block_igat_learningstyles ON 
                mdl_badge.courseid = mdl_block_igat_learningstyles.courseid 
                AND mdl_badge_issued.userid = mdl_block_igat_learningstyles.userid 
              WHERE mdl_badge.courseid = " . $this->courseId . " 
                AND processing >= $processingMin AND processing <= $processingMax
                AND perception >= $perceptionMin AND perception <= $perceptionMax
                AND input >= $inputMin AND input <= $inputMax
                AND comprehension >= $comprehensionMin AND comprehension <= $comprehensionMax 
              GROUP BY mdl_badge.id, mdl_badge.courseid, mdl_badge_issued.userid
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
   * $data the data to user
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