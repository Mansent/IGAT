<?php
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
      $this->userId = $userId;
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