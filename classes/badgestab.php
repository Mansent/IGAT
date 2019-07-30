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
	//$output = $PAGE->get_renderer('core', 'badges');
    $records = badges_get_badges(2, $courseid);
	//echo '<pre>' . var_export($records, true) . '</pre>';
	
    $badges = json_decode($records, true);
	foreach($records as &$badge) {
		block_igat_badgestab::renderBadge($badge);
	}
   
    //$badges = new \core_badges\output\badge_collection($records);
    //echo $output->render($badges);
  }
  
  private function renderBadge($badge) {
	global $PAGE;
	$id = $badge->id;
	$name = $badge->name;
	$description = $badge->description;
	$criteria = $badge->criteria;
	$completioncriteria = $criteria[1];
	$criteriadescription = $completioncriteria->description;
		
	echo '<div class="igatbadge">';
	echo print_badge_image($badge, $PAGE->context, "f3");
	echo "<h3>$id: $name</h3>";
	echo "<p>$description<p>";
	echo "<p>$criteriadescription</p>";
	echo '</div>';
  }
}
?>