<?php
/**
 * Library for loading the player ranks for the leaderboard.
 */
class igat_ranks
{
  private $courseId;
  
  /**
   * Creates a new igat statistics object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }
  
  /**
   * Loads the full leaderboard from the database including user names and their badges 
   * @return an array containing all leaderboard information
   */
  function getLeaderboard() {
    global $DB;
    
    $lib_badges = new igat_badges($this->courseId);
    $records = $DB->get_records('block_xp', array('courseid' => $this->courseId), '`xp` DESC'); 

    foreach($records as &$user) {
      // load user names 
      $user_record = $DB->get_record('user', array('id' => $user->userid));
      $user->firstname = $user_record->firstname;
      $user->lastname = $user_record->lastname;
      
      // load user badges
      $badges = $lib_badges->getUserBadges($user->userid);
      $user_badges = array();
      foreach($badges as &$badge) {
        if($badge->dateissued != null) {
          array_push($user_badges, $badge);
        }
      }
      $user->badges = $user_badges;
      
      // cleanup unnecessary information
      unset($user->courseid);
      unset($user->id);
    }
    return $records;
  }
  
  /*
   * Constructs a message that tells the user how many points he needs to reach a better place in the leaderboard.
   * @param int $userId the id of the user this message is for
   * @return string the message created
   */
  function getRanksStatusMessage($userId) {
    global $DB;
    $userPoints = $DB->get_record('block_xp', array('userid' => $userId, 'courseid' => $this->courseId))->xp;
    if($userPoints === null) {
      return "";
    }
    
    $userBelow = $DB->get_record_sql("SELECT * FROM mdl_block_xp WHERE courseid = $this->courseId AND xp < $userPoints ORDER BY xp DESC LIMIT 1;");
    $userAbove = $DB->get_record_sql("SELECT * FROM mdl_block_xp WHERE courseid = $this->courseId AND xp > $userPoints ORDER BY xp ASC LIMIT 1;");
    
    if($userAbove != null) {
      $pointDiff = $userAbove->xp - $userPoints;
      $user_record = $DB->get_record('user', array('id' => $userAbove->userid));
      $user_record = $DB->get_record('user', array('id' => $userAbove->userid));
      return $pointDiff . " points left to catch up <b>" . $user_record->firstname . " " . $user_record->lastname . "</b>";
    }
    else {
      return "Your are <b>number one!</b>";
    }
  }
}
?>