<?php
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
		
		$anonymous_leaderboard = 0;
		$leaderboard_display = 'limited';
		
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
		
		$usersettings = array ('anonymous_leaderboard' => ($record->anonymousleaderboard == 1), 
													 'leaderboard_display' => $record->leaderboarddisplay);
		return $usersettings;
	}
}
?>