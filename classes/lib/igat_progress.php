<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Library for managing user points and levels.
 */
class igat_progress
{
  private $courseId;
  private $userInfo;
  private $userInfoId;
  private $levelsInfo;
    
  /**
   * Creates a new igat progress object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }
  
  /** 
   * @return stdClass a userInfo object containing the current user's points and level.
   */
  public function getCurrentUserInfo() {
    global $USER;
    return $this->getUserInfo($USER->id);
  }
	
  /** 
   * @param int $userId the user to get the points and level of
   * @return stdClass a userInfo object containing the user's points and level.
   */
	public function getUserInfo($userId) {
    global $DB;
    if($this->userInfo == null || $this->userInfoId != $userId) {
      $this->userInfo = $DB->get_record('block_xp', array('courseid' => $this->courseId, 'userid' => $userId)); 
      $this->userInfoId = $userId;
    }
    return $this->userInfo;    
  }
  
  /**
   * Loads information about the levels from the database
   * @return stdClass a levels info object containing all points required for the levels
   */
  public function getLevelsInfo() {
    global $DB;
    
    if($this->levelsInfo == null) {
      $config = $DB->get_record('block_xp_config', array('courseid' => $this->courseId)); 
      $configJson = json_decode($config->levelsdata, true);
      $this->levelsInfo = $configJson['xp'];
    }
    
    return $this->levelsInfo;
  }
  
  /**
   * @return int the level of the current user 
   */
  public function getCurrentUserLevel() {
    return $this->getCurrentUserInfo->xp;
  }
  
  /**
   * @return int the number of levels reachable in this gamification
   */
  public function getNumLevels() {
    $levelsInfo = $this->getLevelsInfo();
    return count($levelsInfo);   
  }
  
  /**
   * @param int $userId the id of the user
   * @return double the relative progress of the user in the current level or 1 if he reached the highest level
   */
  public function getCurrentLevelProgress($userId) {
    if($this->userReachedMaxLevel($userId)) {
      return 1;
    }
    
    $userInfo = $this->getUserInfo($userId);
    $points = $userInfo->xp;
    $level = $userInfo->lvl;
    
    $levelsInfo = $this->getLevelsInfo($userId);
    $curLevelPoints = $levelsInfo[$level];
    $nextLevelPoints = $levelsInfo[$level + 1];
    $progress = ($points - $curLevelPoints) / ($nextLevelPoints - $curLevelPoints);
    return $progress;
  }
	
	/**
	 * Checks for which assignments and quizzes the user can earn points and generates short info messages 
   * @param int $userId the id of the user
   * @return the assignments and quizzes that the user can complete to earn points
   */
	public function getOpenActivities($userId) {
		global $DB, $CFG;
		$result = array();
		//get rules for points from db
		$ruledata = $DB->get_records('block_xp_filters', array('courseid' => $this->courseId));
		foreach($ruledata as &$rule) {
			$points = $rule->points;
			$ruledataJson = json_decode($rule->ruledata, true);
			
			if($ruledataJson['method'] == 'any') { //rules of type 'all' or 'none' are not supported
				foreach($ruledataJson['rules'] as &$condition) {
					if($condition['_class'] == 'block_xp_rule_cm') { // only consider activity conditions
						$conditionContextId = $condition['value'];
						
						//get module info
						$activityInfo = $DB->get_record_sql('SELECT ' . $CFG->prefix . 'modules.name, ' . $CFG->prefix . 'course_modules.instance FROM ' . $CFG->prefix . 'context 
								INNER JOIN ' . $CFG->prefix . 'course_modules ON ' . $CFG->prefix . 'context.instanceid = ' . $CFG->prefix . 'course_modules.id 
								INNER JOIN ' . $CFG->prefix . 'modules ON ' . $CFG->prefix . 'course_modules.module = ' . $CFG->prefix . 'modules.id 
							WHERE ' . $CFG->prefix . 'context.id = ' . $conditionContextId . ';');
						
						$activityType = $activityInfo->name;
						$activityId = $activityInfo->instance;
						
						//test if assigment or quiz is completed
						if($activityType == "assign") {
							$assignmentName = $DB->get_record('assign', array('id' => $activityId))->name;
							$gradesCount = $DB->count_records_sql("SELECT COUNT(*) FROM " . $CFG->prefix . "assign_grades 
								WHERE assignment = " . $activityId . " AND userid = " . $userId);
								
							if($gradesCount == 0) { //the assignment has not been completed yet
								array_push($result, 'Complete assignment <i>' . $assignmentName . '</i> to earn <b>' . $points . ' points</b>');
							}
						}
						else if($activityType == 'quiz') {
							$quizName = $DB->get_record('quiz', array('id' => $activityId))->name;
							$gradesCount = $DB->count_records_sql("SELECT COUNT(*) FROM " . $CFG->prefix . "quiz_grades 
								WHERE quiz = " . $activityId . " AND userid = " . $userId);
								
							if($gradesCount == 0) { //the quiz has not been completed yet
								array_push($result, 'Complete quiz <i>' . $quizName . '</i> to earn <b>' . $points . ' points</b>');
							}
						}
					}
				}
			}
		}
		return $result;
	}
  
  /**
   * @param int $userId the id of the user
   * @return int how many points are required for the user to reach the next level
   */
  public function getPointsToNextLevel($userId) {
    if($this->userReachedMaxLevel($userId)) {
      return 0;
    }
    
    $userInfo = $this->getUserInfo($userId);
    $points = $userInfo->xp;
    $level = $userInfo->lvl;
    
    $levelsInfo = $this->getLevelsInfo($userId);
    $nextLevelPoints = $levelsInfo[$level + 1];
    $pointsToNextLevel = $nextLevelPoints - $points;
    return $pointsToNextLevel;
  }
  
  /**
   * @return boolean if the user has reached the highest level
   */
  public function userReachedMaxLevel($userId) {
    $userInfo = $this->getUserInfo($userId);
    $level = $userInfo->lvl;
    $numLevels = $this->getNumLevels();
    return $level == $numLevels;
  }
}
?>