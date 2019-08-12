<?php
require_once('classes/lib/igat_badges.php');

/**
 * Library for loading the player ranks for the leaderboard.
 */
class igat_ranks
{
  private $courseId;
  private $lib_badges;
  
  /**
   * Creates a new igat statistics object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
    $this->lib_badges = new igat_badges($courseId);
  }
  
  /**
   * Loads the full leaderboard from the database including user names and their badges 
   * @return an array containing all leaderboard information
   */
  function getLeaderboard() {
    global $DB;
    $records = $DB->get_records('block_xp', array('courseid' => $this->courseId), '`xp` DESC'); 

    foreach($records as &$user) {
      // load user names 
      $user_record = $DB->get_record('user', array('id' => $user->userid));
      $user->firstname = $user_record->firstname;
      $user->lastname = $user_record->lastname;
      
      // load user badges
      $badges = $this->lib_badges->getUserBadges($user->userid);
      $user_badges = array();
      foreach($badges as &$badge) {
        if($badge->dateissued != null) {
          array_push($user_badges, $badge);
        }
      }
      $user->badges = &$user_badges;
      
      // cleanup unnecessary information
      unset($user->courseid);
      unset($user->id);
    }
    return $records;
  }
}
?>