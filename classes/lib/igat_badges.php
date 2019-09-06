<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/badgeslib.php');

/**
 * Provides an interface for this plugin to the moodle badges system.
 */
class igat_badges 
{
  private $courseId;
  private $currentBadges;
  private $currentUserId;
  
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
    /*if($this->currentUserId == $userId) {
      return $this->currentBadges;
    }
	$this->currentUserId = $userId;*/
		
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
   * Gets the url of a badge information page
	 * @param badge $badge the badge to get the link for 
   * @return the url to the badge information page
   */
  public function getBadgePageUrl(badge $badge) {
	  return "/badges/badge.php?hash=" . $badge->uniquehash;
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
  
  /**
   * @return int the number of badges that are potentially available to earn for the user
   */
  public function getNumAvailableBadges() {
    return count($this->getCurrentUserBadges());
  }
  
  /**
   * @param int $userId the id of the user to load the badges for
   * @return string a random criterion for an open badge of the user
   */
  public function getRandomOpenBadgeCriterion($userId) {
    $badges = $this->getUserBadges($userId);
    // load open badges
    $openBadges = array();
    $i = 0;
    foreach($badges as &$badge) {
      if($badge->dateissued == null) {
        array_push($openBadges, $badge);
      }
    }
    
    //no open badges?
    $numOpenBadges = count($openBadges);
    if($numOpenBadges == 0) {
      return "You earned all badges!";
    }
    
    //get random badge criterion
    $randBadge = $openBadges[array_rand($openBadges)];
    end($randBadge->criteria);
    return strip_tags($randBadge->criteria[key($randBadge->criteria)]->description) . ' to <b>earn a badge!</b>';
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