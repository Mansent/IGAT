<?php
require_once($CFG->libdir . '/badgeslib.php');

/**
 * Provides an interface for this plugin to the moodle badges system.
 */
class igat_badges 
{
  private $courseId;
  private $currentBadges;
  
  /**
   * Creates a new badge library object.
   * @param int $courseId the id of the current moodle course
   */
  function __construct($courseId) {
    $this->courseId = $courseId;
  }
  
	/**
	 * Loads all available badges for the current user from the database. It can be checked 
	 * if the user earns this badge by testing if $badge->dateissued != null.
	 * @return array $badge all badges that are visible to this student
	 */
	public function getCurrentUserBadges()
	{
		global $USER;
    return $this->getUserBadges($USER->id);
	}
  
  /**
	 * Loads all available badges for a user from the database. It can be checked 
	 * if the user earns this badge by testing if $badge->dateissued != null.
   * @param int $userId the id of the user to load the badges for
	 * @return array $badge all badges that are visible to this student
	 */
  public function getUserBadges($userId) {
    // Buffer result to save db queries for multiple function calls
    if($this->currentBadges != null) {
      return $this->currentBadges;
    }
		
    // Only use default parameters
		$type = 2;
		$sort = '';
		$dir = '';
		$page = 0;
		$perpage = 1000;
		$badges = badges_get_badges($type, $this->courseId, $sort, $dir, $page, $perpage, $userId);
    $this->currentBadges = &$badges;
		
		return $badges;
  }
	
	/** 
	 * Looks up the url for a badge image
	 * @param badge $badge the badge to load the image for 
	 * @return string the url to the image of this badge 
	 */
	public function getBadgeImageURL(badge $badge) {
		global $PAGE;
		$context = $PAGE->context;
		$fsize = 'f1';
		$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', $fsize, false);
		return $imageurl;
  }
  
  /**
   * Gets the number of badges the current user owns
   * @return int the number of badges
   */
  public function getNumUserBadges() {
		$badges = $this->getCurrentUserBadges();

    $counter = 0;
    foreach($badges as &$badge) {
			if($badge->dateissued != null) { // user owns badge
        $counter++;
      }
    }
    
    return $counter;
  }
  
  public function getNumAvailableBadges() {
    return count($this->getCurrentUserBadges());
  }
	
	/**
	 * @return the moodle core renderer for badges
	 */
	public function getCoreRenderer() {
		global $PAGE;
		$core_renderer = $PAGE->get_renderer('core', 'badges');
		return $core_renderer;
	}
}
?>