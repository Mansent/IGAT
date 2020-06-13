<?php
defined('MOODLE_INTERNAL') || die();

require_once('igat_capabilities.php');

/**
 * Library for loading the player ranks for the leaderboard.
 */
class igat_ranks
{
  private $courseId;
	private $limitDistance = 5;
  private $lib_capabilities;
  
  /**
   * Creates a new igat statistics object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
    $this->lib_capabilities = new igat_capabilities();
  }
  
	function getLeaderboard($userId) {
		$lib_usersettings = new igat_usersettings($this->courseId);
    $usersettings = $lib_usersettings->getUsersettings($userId);
    $leaderboard = $this->loadLeaderboard();
    
    // find current user
    $currentUserIndex = -1;
    for($i=0; $i<count($leaderboard); $i++) {
      if($leaderboard[$i]->userid == $userId) {
        $currentUserIndex = $i;
        break;
      }
    }
    
    // find users with equal points to current user and move current user to top
    if($currentUserIndex > -1) {
      $firstUserEqualPointsIndex = -1;
      $currentUserPoints = $leaderboard[$currentUserIndex]->xp;
      for($i=$currentUserIndex; $i>=0; $i--) {
        if($leaderboard[$i]->xp == $currentUserPoints) {
          $firstUserEqualPointsIndex = $i;
        }
      }
      //swap ranks
      $temp = $leaderboard[$firstUserEqualPointsIndex]->rank;
      $leaderboard[$firstUserEqualPointsIndex]->rank = $leaderboard[$currentUserIndex]->rank;
      $leaderboard[$currentUserIndex]->rank = $temp;
      
      //swap array
      $temp = $leaderboard[$firstUserEqualPointsIndex];
      $leaderboard[$firstUserEqualPointsIndex] = $leaderboard[$currentUserIndex];
      $leaderboard[$currentUserIndex] = $temp;
      $currentUserIndex = $firstUserEqualPointsIndex;
    }
		
    $isTeacher = $this->lib_capabilities->isManagerOrTeacher($this->courseId, $userId);
		if($usersettings->leaderboarddisplay == 'all' || $isTeacher) {
			return $leaderboard;
		}
		else if($usersettings->leaderboarddisplay == 'limited') {
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
    global $DB, $CFG;
    
    $lib_badges = new igat_badges($this->courseId);
		$lib_usersettings = new igat_usersettings($this->courseId);
		
		// Join xp table with enrolments to ensure only enrolled users are in the leaderboard
		$sql = "SELECT * FROM " . $CFG->prefix . "block_xp 
						INNER JOIN ( 
							SELECT " . $CFG->prefix . "user_enrolments.userid, " . $CFG->prefix . "enrol.courseid FROM  " . $CFG->prefix . "user_enrolments 
							INNER JOIN " . $CFG->prefix . "enrol 
							ON " . $CFG->prefix . "user_enrolments.enrolid  = " . $CFG->prefix . "enrol.id 
						) AS enrolments
						ON " . $CFG->prefix . "block_xp.userid = enrolments.userid AND " . $CFG->prefix . "block_xp.courseid = enrolments.courseid 
						WHERE " . $CFG->prefix . "block_xp.courseid = " . $this->courseId . " ORDER BY `xp` DESC";
    $records = $DB->get_records_sql($sql); 

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
    $curUsersettings = $lib_usersettings->getUsersettings($userId);
    $userPoints = $DB->get_record('block_xp', array('userid' => $userId, 'courseid' => $this->courseId))->xp;
    if($userPoints === null) {
      return "";
    }
    
    $userBelow = $DB->get_record_sql("SELECT * FROM " . $CFG->prefix . "block_xp WHERE courseid = $this->courseId AND xp < $userPoints ORDER BY xp DESC LIMIT 1;");
    $userAbove = $DB->get_record_sql("SELECT * FROM " . $CFG->prefix . "block_xp WHERE courseid = $this->courseId AND xp > $userPoints ORDER BY xp ASC LIMIT 1;");
    
    if($userAbove != null) {
      $pointDiff = $userAbove->xp - $userPoints;
      $usersettings = $lib_usersettings->getUsersettings($userAbove->userid);
      if($curUsersettings->anonymousleaderboard) { // the current user has set leaderboard settings to anonymous
        return $pointDiff . " XP's left to catch up to <b>your peer</b>";
      }
      else if($usersettings->anonymousleaderboard) { // the user above has set leaderboard settings to anonymous
        return $pointDiff . " XP's left to catch up to <b>anonymous user</b>";
      }
      else {
        $user_record = $DB->get_record('user', array('id' => $userAbove->userid));
        return $pointDiff . " XP's left to catch up <b>" . $user_record->firstname . " " . $user_record->lastname . "</b>";
      }
    }
    else {
      return "Your are <b>number one!</b>";
    }
  }
}
?>