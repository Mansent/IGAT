<?php
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
}