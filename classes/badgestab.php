<?php

require_once($CFG->libdir . '/badgeslib.php');


/**
 * Responsible for managing and rendering the badges tab in the gamification view 
 */
class block_igat_badgestab {
  
  /**
   * Renders the badges tab
   * @param courseid the id of the current course.
   */
  public function render_tab($courseid) {
	global $USER;
	
	$records = badges_get_badges(2, $courseid, '', '', 0, 1000, $USER->id);
	
	echo '<h2>Your Badges</h2>';
	foreach($records as &$badge) {
		if($badge->dateissued != null) { // user owns badge
			block_igat_badgestab::renderBadge($badge);
		}
	}
	
	echo '<h2>Available Badges</h2>';
	echo '<div class="igatbadgescontainer">';
	foreach($records as &$badge) {
		if($badge->dateissued == null) { // user has not yet achieved badge
			block_igat_badgestab::renderBadge($badge);
		}
	}
	echo '</div>';
  }
  
  private function renderBadge($badge) {
	global $PAGE;
	$id = $badge->id;
	$name = $badge->name;
	$description = $badge->description;
	$uniquehash = $badge->uniquehash;
	$userOwnsBadge = $badge->dateissued != null;
	$criteria = $badge->criteria;
	$completioncriteria = $criteria[1];
	$criteriadescription = $completioncriteria->description;
	
	// url to badge page only available if user owns badge
	$badgeUrl = null;
	if($userOwnsBadge) {
		$badgeUrl = "/badges/badge.php?hash=" . $uniquehash;
	}
	
	//determine background color
	$colorClass = null;
	if($userOwnsBadge) {
		$colorClass = "igatbadgeowned";
	}
	else {
		$colorClass = "igatbadgeavailable";
	}
	
	if($userOwnsBadge) echo '<a href="' . $badgeUrl . '">';
	echo '<div class="igatbadge ' . $colorClass . '">';
	echo print_badge_image($badge, $PAGE->context, "f3");
	echo "<h3>$id: $name</h3>";
	if ($userOwnsBadge) {
		echo "<p>$description<p>";
	}
	else {
		echo "<p>$criteriadescription</p>";
	}
	echo '</div>';
	if ($userOwnsBadge) echo '</a>';
  }
}
?>