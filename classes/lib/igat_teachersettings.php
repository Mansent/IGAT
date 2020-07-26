<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for loading and storing the user settings for teachers.
 */
class igat_teachersettings
{    
	private $courseId;
	
  /**
   * Creates a new igat teachersettings object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }

	/**
	 * Inserts a new record with the default teacher settings in the database.
	 */
	private function createDefaultTeachersettings() {
		global $DB, $USER;
		$lib_capabilities = new igat_capabilities();
    
    if($lib_capabilities->isManagerOrTeacher($this->courseId, $USER->id)) {
			$DB->insert_record('block_igat_teachersettings', array('courseid' => $this->courseId, 'default_analytics_start' => null, 
			'default_analytics_end' => null));
		}		
	}
	
	/**
	 * Loads the user settings for a user from the database
	 * @return the loaded settings from the database
	 */
	public function getTeachersettings() {
		global $DB, $CFG;
		
    $sql = "SELECT FROM_UNIXTIME(default_analytics_start) AS defaultanalyticsstart, FROM_UNIXTIME(default_analytics_end) AS defaultanalyticsend 
            FROM " . $CFG->prefix . "block_igat_teachersettings WHERE courseid = " . $this->courseId . ";";
		$record = $DB->get_record_sql($sql);
		if($record === false) { //there are currently no user settings for this user in db
			$this->createDefaultTeachersettings();
			return $this->getTeachersettings();
		}
		
    $defaultstart = "";
    if($record->defaultanalyticsstart != null) {
      $defaultstart = substr($record->defaultanalyticsstart, 0, 10);
    }
    $defaultend = "";
    if($record->defaultanalyticsend != null) {
      $defaultend = substr($record->defaultanalyticsend, 0, 10);
    }
    
		$teachersettings = (object)array ('default_analytics_start' => $defaultstart, 
                                      'default_analytics_end' => $defaultend);
		return $teachersettings;
	}
	
	/**
	 * Saves the user settings for a user in the database
	 * @param $defaultStart the date that should be selected in the gamification analytics view as default for the start date
	 * @param $defaultEnd the date that should be selected in the gamification analytics view as default for the end date
	 */
	public function saveTeachersettings($defaultStart, $defaultEnd) {
		global $DB, $CFG;
    
    $sqlStart = "UNIX_TIMESTAMP('" . $defaultStart . "')";
    $sqlEnd = "UNIX_TIMESTAMP('" . $defaultEnd . "')";
    if($defaultStart == null) {
      $sqlStart = "NULL";
    }
    if($defaultEnd == null) {
      $sqlEnd = "NULL"; 
    }
    
		
		$sql = "UPDATE " . $CFG->prefix . "block_igat_teachersettings
			SET 
				default_analytics_start = " . $sqlStart . ",
				default_analytics_end = " . $sqlEnd . "
			WHERE courseid='" . $this->courseId . "'";
		$DB->execute($sql);
    return true;
	}
}
?>