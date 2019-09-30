<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for loading and storing the user settings.
 */
class igat_usersettings
{    
	private $courseId;
	
  /**
   * Creates a new igat progress object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }

	/**
	 * Inserts a new record with the default user settings in the database.
	 * @param int $userId the id of the user to insert for
	 */
	private function createDefaultUserSettings($userId) {
		global $DB;
		$lib_capabilities = new igat_capabilities();
		
		$anonymous_leaderboard = 0;
		if($lib_capabilities->isManagerOrTeacher($this->courseId, $userId)) {
			$leaderboard_display = 'all';
		}
		else {
			$leaderboard_display = 'limited';
		}
		
		$DB->insert_record('block_igat_usersettings', array('courseid' => $this->courseId, 'userid' => $userId, 
			'anonymousleaderboard' => $anonymous_leaderboard, 'leaderboarddisplay' => $leaderboard_display));
	}
	
	/**
	 * Loads the user settings for a user from the database
	 * @param int $userId the user to load the settings for
	 * @return the loaded settings from the database
	 */
	public function getUsersettings($userId) {
		global $DB;
		
		$record = $DB->get_record('block_igat_usersettings', array('courseid' => $this->courseId, 'userid' => $userId));
		if($record === false) { //there are currently no user settings for this user in db
			$this->createDefaultUserSettings($userId);
			return $this->getUsersettings($userId);
		}
		
		$usersettings = (object)array ('anonymousleaderboard' => $record->anonymousleaderboard, 
																	 'leaderboarddisplay' => $record->leaderboarddisplay);
		return $usersettings;
	}
	
	/**
	 * Saves the user settings for a user in the database
	 * @param int $userId the user to save the settings for
	 * @param usersettings $usersettings the usersettings object to save for this user
	 */
	public function saveUsersettings($userId, $usersettings) {
		global $DB, $CFG;
		
		$sql = "UPDATE " . $CFG->prefix . "block_igat_usersettings 
			SET 
				anonymousleaderboard = '" . $usersettings->anonymousleaderboard . "',
				leaderboarddisplay = '" . $usersettings->leaderboarddisplay . "'
			WHERE courseid='" . $this->courseId . "' AND userid='" . $userId . "'";
			
		$DB->execute($sql);
	}
}
?>