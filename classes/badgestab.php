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
    global $PAGE;
    
    $output = $PAGE->get_renderer('core', 'badges');
    $records = badges_get_badges(2, $courseid);
   
    $badges = new \core_badges\output\badge_collection($records);
    echo $output->render($badges);
  }
}
?>