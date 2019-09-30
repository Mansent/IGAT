<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for logging user behavior data and managing logs.
 */
class igat_logging
{
	/**
	 * Writes a visit of the gamification dashboard to the logfiles
	 * @param int $courseId the id of the course the user visited
	 * @param int $userId the id of the user visited the page
	 * @param int $loadtime the unix time the user has loaded the page
	 * @param int $leavetime the unix time the user left the page
	 * @param string $url the url of the page the user visited
	 * @param string $destination the url the user opened after leaving this page or null if unknown
	 */
  public function logDashboardVisit($courseId, $userId, $loadtime, $leavetime, $url, $destination) {
		global $DB;
		
		// parse tab
		$tab = substr($url, strrpos($url, '=') + 1);
		if(!in_array($tab, ['progress', 'badges', 'ranks', 'settings'], true)) {
			return; // this should never happen
		}
		
		// calculate duration
		$duration = $leavetime - $loadtime;
		
		// parse next page
		$nextPageURL = parse_url($destination, PHP_URL_PATH);
		if($nextPageURL == "/blocks/igat/dashboard.php") { // another tab was opened
			$nextPageParams = parse_url($destination, PHP_URL_QUERY);
			$newTab =  substr($destination, strrpos($destination, '=') + 1);
			
			if(in_array($newTab, ['progress', 'badges', 'ranks', 'settings'], true)) {
				$nextPage = $newTab;
			}
			else { // this should not happen
				$nextPage = 'external';
			}
		}
		else if($destination != 'undefined') { // user went back to a moodle page
			$nextPage = 'moodle';
		}
		else { // user left moodle or closed the browser
			$nextPage = 'external';
		}
		
		// write log to db
		$DB->insert_record('block_igat_dashboard_log', array('courseid' => $courseId,
																												 'userid' => $userId,
																												 'time' => $loadtime, 
																												 'duration' => $duration,
																												 'tab' => $tab,  
																												 'next_page' => $nextPage));
	}
  
  /**
   * Deletes all records from the logs before a given date
   * @param $date the date to delete logs before
   * @param $courseId the id of the course 
   */
  public function deleteLogsBefore($date, $courseId) {
    global $DB, $CFG;
    if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) { // YYYY-MM-DD
      return false;
    }
    
    // Delete block xp logs used for game element statistics
    $sql = "DELETE FROM " . $CFG->prefix . "block_xp_log WHERE time < UNIX_TIMESTAMP('" . $date . "') AND courseid = " . $courseId;
    $DB->execute($sql);
    
    // Delete gamification dashboard view logs
    $sql = "DELETE FROM " . $CFG->prefix . "block_igat_dashboard_log WHERE time < (UNIX_TIMESTAMP('" . $date . "') * 1000) AND courseid = " . $courseId;
    $DB->execute($sql);
    
    // Delete moodle logs used for gamification feedback rate calculation
    $sql = "DELETE FROM " . $CFG->prefix . "logstore_standard_log WHERE timecreated < UNIX_TIMESTAMP('" . $date . "') AND courseid = " . $courseId;
    $DB->execute($sql);
    
    return true;
  }
  
  /**
   * Deletes all records from the logs after a given date
   * @param $date the date to delete logs after
   * @param $courseId the id of the course 
   */
  public function deleteLogsAfter($date, $courseId) {
    global $DB, $CFG;
     if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date)) { // YYYY-MM-DD
      return false;
    }
    // Delete block xp logs used for game element statistics
    $sql = "DELETE FROM " . $CFG->prefix . "block_xp_log WHERE time > UNIX_TIMESTAMP('" . $date . "') AND courseid = " . $courseId;
    $DB->execute($sql);
    
    // Delete gamification dashboard view logs
    $sql = "DELETE FROM " . $CFG->prefix . "block_igat_dashboard_log WHERE time > (UNIX_TIMESTAMP('" . $date . "') * 1000) AND courseid = " . $courseId;
    $DB->execute($sql);
    
    // Delete moodle logs used for gamification feedback rate calculation
    $sql = "DELETE FROM " . $CFG->prefix . "logstore_standard_log WHERE timecreated > UNIX_TIMESTAMP('" . $date . "') AND courseid = " . $courseId;
    $DB->execute($sql);
    return true;
  }
}