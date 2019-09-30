<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for checking notifications.
 */
class igat_notification
{
	/**
	 * Checks if there is a notification and returns it if there is any
	 * @param int $courseId the id of the course to check for
	 * @param int $userId the id of the user to check for
	 * @return the notification or false if ther is no new notification
	 */
	public function getNotification($courseId, $userId) {
		global $DB, $CFG;
    
    $sql = 'SELECT * FROM ' . $CFG->prefix . 'block_igat_notifications 
                                    WHERE courseid = ' . $courseId . ' 
                                    AND userid = ' . $userId . ' 
                                    AND processed = 0';
    $record = $DB->get_record_sql($sql);
    if($record == false) {
      return false;
    }
    
    $sql = "UPDATE " . $CFG->prefix . "block_igat_notifications SET processed = 1 WHERE id = " . $record->id;
    $DB->execute($sql); 
    return $record;
	}
}