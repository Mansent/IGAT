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
  private $imageFiles;
  private $plusInstalled;
    
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
      $this->levelsInfo = $configJson;
    }
    return $this->levelsInfo['xp'];
  }
  
  /**
   * Loads information about the levels from the database
   * @return stdClass a levels info object containing all points required for the levels, level names and descriptions
   */
  public function getFullLevelsInfo() {
    global $DB;
    
    if($this->levelsInfo == null) {
      $config = $DB->get_record('block_xp_config', array('courseid' => $this->courseId)); 
      $configJson = json_decode($config->levelsdata, true);
      $this->levelsInfo = $configJson;
    }
    return $this->levelsInfo;
  }
  
  /**
   * Loads the images of the lecels from the database
   * @returns array an array containing the links to the level images for each level
   */
  public function getLevelsImages() {
    global $DB, $PAGE;
    if($this->imageFiles == null) {
      $imageFiles = [];
      
      //start with default image for each level
      $levelsInfo = $this->getLevelsInfo();
      foreach($levelsInfo as $level => $info) {
        $imageFiles[$level] = '/blocks/igat/img/level.png';
      }
      
      if(!$this->hasLevelUpPlus()) {
      echo 'affe';
        $this->imageFiles = $imageFiles;
        return $this->imageFiles; // custom badges only available in level up plus
      }
      
      //load preset badges 
      $levelupplus_config = $DB->get_record('local_xp_config', array('courseid' => $this->courseId)); 
      $badgetheme = $levelupplus_config->badgetheme;
      for($i = 1; $i <= 10; $i++) {
        $imageFiles[$i] = '/local/xp/pix/theme/' . $badgetheme . '/' . $i . '.png';
      }
      
      // load custom badges configuration
      $levelup_config = $DB->get_record('block_xp_config', array('courseid' => $this->courseId)); 
      if($levelup_config->enablecustomlevelbadges) {
        //load custom image files
        $fs = get_file_storage();
        $allfiles = $fs->get_area_files($PAGE->context->id, 'block_xp', 'badges');

        foreach ($allfiles as $file) {
            if (strpos($file->get_mimetype(), 'image/') !== 0) {
                continue;
            }
            $matches = [];
            if (!preg_match('~^(\d+)\.[a-z]+$~i', $file->get_filename(), $matches)) {
                continue;
            }
            $i = (int) $matches[1];
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
            $imageFiles[$i] = $url;
        }
        $this->imageFiles = $imageFiles;
      }
    }
    return $this->imageFiles;
  }
  
  /**
   * @return the url of the level image of the current users level
   */
  public function getCurrentUserLevelImage() {
    $userLevel = $this->getCurrentUserInfo()->lvl;
    return $this->getLevelsImages()[$userLevel];
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
   * If there are more than 10 messages, only a random subset of these will be returned
   * @param int $userId the id of the user
   * @return the assignments and quizzes that the user can complete to earn points
   */
	public function getOpenActivities($userId) {
		global $DB, $CFG;
		$result = array();
		//get rules for points from db
		$ruledata = $DB->get_records('block_xp_filters', array('courseid' => $this->courseId));
		foreach($ruledata as &$rule) {
      $ruleconditions = array();
			$points = $rule->points;
			$ruledataJson = json_decode($rule->ruledata, true);
			
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
              array_push($ruleconditions, 'assignment <i>' . $assignmentName . '</i>');
            }
          }
          else if($activityType == 'quiz') {
            $quizName = $DB->get_record('quiz', array('id' => $activityId))->name;
            $gradesCount = $DB->count_records_sql("SELECT COUNT(*) FROM " . $CFG->prefix . "quiz_grades 
              WHERE quiz = " . $activityId . " AND userid = " . $userId);
              
            if($gradesCount == 0) { //the quiz has not been completed yet
              array_push($ruleconditions, 'quiz <i>' . $quizName . '</i>');
            }
          }
        }
      }
      
      if($ruledataJson['method'] == 'all' || $ruledataJson['method'] == 'none') {
        //Combine to rule string
        $resString = ''; 
        foreach ($ruleconditions as &$rulecondition) {
          if($resString != '') {
            $resString = $resString . ' and ';
          }
          if($ruledataJson['method'] == 'all') {
            $resString = $resString . ' complete ' . $rulecondition;
          }
          else if($ruledataJson['method'] == 'none') {
            $resString = $resString . ' not complete ' . $rulecondition;
          }
        }
				if($resString != '') {
					$resString = $resString . ' to earn <b>' . $points . ' points </b>';
					$resString = ucfirst(trim($resString));
					array_push($result, $resString);
				}
      }
      else if($ruledataJson['method'] == 'any') {
        foreach ($ruleconditions as &$rulecondition) {
          array_push($result, 'Complete ' . $rulecondition . ' to earn <b>' . $points . ' points </b>');
        }
      }
		}
    
    if(count($result) <= 10) {
      return $result;
    }
    else {
      //Pick 10 random rules to display to the user
      $keys = array_rand($result, 10);
      $randomResult = array();
      foreach ($keys as $key) {
          $randomResult[] = $result[$key];
      }
      return $randomResult;
    }
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
  
  /**
   *  @return boolean if the Level up! Plus is installed
   */
  public function hasLevelUpPlus() {
    global $CFG, $DB;
    if(!isset($this->plusInstalled)) {
      $records = $DB->get_records_sql("SHOW TABLES LIKE '" . $CFG->prefix . "local_xp_config'"); // check if table for level up plus exists in db
      $this->plusInstalled = (count($records) > 0);
    }
    return $this->plusInstalled;
  }
}
?>