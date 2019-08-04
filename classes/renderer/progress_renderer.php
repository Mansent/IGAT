<?php

/**
 * Responsible for managing and rendering the levels tab in the gamification view 
 */
class progress_renderer {
  
  /**
   * Renders the levels tab
   * @param courseid the id of the current course.
   */
  public function render_tab($courseid) {
    global $DB, $USER;
    
    $userinfo = $DB->get_record('block_xp', array('courseid' => $courseid, 'userid' => $USER->id)); //SQL query	?>
	
	<h3>Level: <?php echo $userinfo->lvl; ?></h3>
	<h4>Points: <?php echo $userinfo->xp; ?></h4>

<?php
  }
}
?>