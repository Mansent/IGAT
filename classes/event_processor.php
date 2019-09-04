<?php

namespace block_igat;

/**
 * This class is responsible for processing all registered events
 */
class event_processor {
  
  /**
   * Processes the level up event by writing a log entry to the levelup_log table
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
      
    return true;
  }
  
}