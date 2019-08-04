<?php
require_once($CFG->libdir . '/badgeslib.php');

/**
 * Provides an interface for this plugin to the moodle badges system.
 */
class igat_badges {
	
	/**
	 * Loads all available badges for the user from the database. It can be checked 
	 * if the user earns this badge by testing if $badge->dateissued != null.
	 * @param int $courseId the id of the current course
	 * @return array $badge all badges that are visible to this student
	 */
	public function getCurrentUserBadges($courseId)
	{
		global $USER;
		
		$type = 2;
		$sort = '';
		$dir = '';
		$page = 0;
		$perpage = 1000;
		$badges = badges_get_badges($type, $courseid, $sort, $dir, $page, $perpage, $USER->id);
		
		return $badges;
	}
	
	/** 
	 * Looks up the url for a badge image
	 *  @param badge $badge the badge to load the image for 
	 * 	@return string the url to the image of this badge 
	 */
	public function getBadgeImageURL(badge $badge) {
		global $PAGE;
		$context = $PAGE->context;
		$fsize = 'f1';
		$imageurl = moodle_url::make_pluginfile_url($context->id, 'badges', 'badgeimage', $badge->id, '/', $fsize, false);
		return $imageurl;
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