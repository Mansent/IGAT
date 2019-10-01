<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for loading the player ranks for the leaderboard.
 */
class igat_ranks
{
  private $courseId;
	private $limitDistance = 5;
  
  /**
   * Creates a new igat statistics object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }
  
	function getLeaderboard($userId) {
		$lib_usersettings = new igat_usersettings($this->courseId);
		$usersettings = $lib_usersettings->getUsersettings($userId);
		
		if($usersettings->leaderboarddisplay == 'all') {
			return $this->loadLeaderboard();
		}
		else if($usersettings->leaderboarddisplay == 'limited') {
			$leaderboard = $this->loadLeaderboard();
			
			// find current user
			$currentUserIndex = -1;
			for($i=0; $i<count($leaderboard); $i++) {
				if($leaderboard[$i]->userid == $userId) {
					$currentUserIndex = $i;
					break;
				}
			}
			if($currentUserIndex == -1) {
				return array(); // User does not have any points in this course
			}
			
			//build limited leaderboard
			$lim_leaderboard = array();
			$startIndex = max(0, $currentUserIndex - $this->limitDistance);
			$endIndex = min($currentUserIndex + $this->limitDistance, count($leaderboard) - 1);
			
			if($startIndex > 0) { 
				$rank = -1 * ($currentUserIndex - $startIndex);
			}
			else {
				$rank = 1; // Users on top of the leaderboard should know that
			}
			for($i = $startIndex; $i<=$endIndex; $i++) {
				if($rank > 0 && startIndex < 0) { // if showing only relative rank use + sign
				  $leaderboard[$i]->rank = '+' . $rank;
				}
				else {
				  $leaderboard[$i]->rank = $rank;
				}
				array_push($lim_leaderboard, $leaderboard[$i]);
				$rank++;
			}
			
			return $lim_leaderboard;
		}
		else
		{
			return array();
		}
	}
	
  /**
   * Loads the full leaderboard from the database including user names and their badges 
   * @return an array containing all leaderboard information
   */
  function loadLeaderboard() {
    global $DB;
    
    $lib_badges = new igat_badges($this->courseId);
		$lib_usersettings = new igat_usersettings($this->courseId);
    $records = $DB->get_records('block_xp', array('courseid' => $this->courseId), '`xp` DESC'); 

		$leaderboard = array();
		$i=0;
    foreach($records as &$user) {
			$usersettings = $lib_usersettings->getUsersettings($user->userid);
			if($usersettings->anonymousleaderboard == 1) {
				$user->firstname = "Anonymous";
				$user->lastname = "";
				$user->anonymous = true;
			}
			else {
				// load user names 
				$user_record = $DB->get_record('user', array('id' => $user->userid));
				$user->firstname = $user_record->firstname;
				$user->lastname = $user_record->lastname;
				$user->anonymous = false;
			}
      $user->rank = $i + 1;
			
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
			
			$leaderboard[$i] = $user;
			$i++;
		}
    return $leaderboard;
  }
  
  /*
   * Constructs a message that tells the user how many points he needs to reach a better place in the leaderboard.
   * @param int $userId the id of the user this message is for
   * @return string the message created
   */
  function getRanksStatusMessage($userId) {
    global $DB, $CFG;
		$lib_usersettings = new igat_usersettings($this->courseId);
    $userPoints = $DB->get_record('block_xp', array('userid' => $userId, 'courseid' => $this->courseId))->xp;
    if($userPoints === null) {
      return "";
    }
    
    $userBelow = $DB->get_record_sql("SELECT * FROM " . $CFG->prefix . "block_xp WHERE courseid = $this->courseId AND xp < $userPoints ORDER BY xp DESC LIMIT 1;");
    $userAbove = $DB->get_record_sql("SELECT * FROM " . $CFG->prefix . "block_xp WHERE courseid = $this->courseId AND xp > $userPoints ORDER BY xp ASC LIMIT 1;");
    
    if($userAbove != null) {
      $pointDiff = $userAbove->xp - $userPoints;
      $usersettings = $lib_usersettings->getUsersettings($userAbove->userid);
      if($usersettings->anonymousleaderboard) {
        return $pointDiff . " points left to catch up to <b>anonymous user</b>";
      }
      else {
        $user_record = $DB->get_record('user', array('id' => $userAbove->userid));
        return $pointDiff . " points left to catch up <b>" . $user_record->firstname . " " . $user_record->lastname . "</b>";
      }
    }
    else {
      return "Your are <b>number one!</b>";
    }
  }
}
?>