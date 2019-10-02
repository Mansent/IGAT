<?php
namespace block_igat;

defined('MOODLE_INTERNAL') || die();

/**
 * This class is responsible for processing all registered events
 */
class event_processor {
  
  /**
   * User level up event
   * Processes the level up event by writing a log entry to the levelup_log and notifications tables
   * @param event $event the event data
   */
  public static function user_level_up(\block_xp\event\user_leveledup $event) {
    global $DB;
    
    //Get event data
    $courseId = $event->courseid;
    $userId = $event->userid;
    $newLevel = $event->other['level'];
    $time = time();
  
    //Write log to db
		$DB->insert_record('block_igat_levelup_log', array('courseid' => $courseId, 'userid' => $userId, 
			'newlevel' => $newLevel, 'time' => $time));
		$DB->insert_record('block_igat_notifications', array('courseid' => $courseId, 'userid' => $userId, 
			'object' => 'level',  'object_id' => $newLevel, 'processed' => '0'));
      
    return true;
  }
  
  /**
   * User earned event
   * Processes the level up event by writing a log entry to the notifications table
   * @param event $event the event data
   */
  public static function user_earned_badge(\core\event\badge_awarded $event) {
    global $DB;
    $badgeId = $event->objectid;
    $courseId = $event->courseid;
    $userId = $event->relateduserid;
    
		$DB->insert_record('block_igat_notifications', array('courseid' => $courseId, 'userid' => $userId, 
			'object' => 'badge',  'object_id' => $badgeId, 'processed' => '0'));
      
    return true;
  }
  
}