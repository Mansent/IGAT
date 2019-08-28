<?php
/**
 * Responsible for gererating and rendering the game elements analytics 
 */
class game_elements_analytics_renderer 
{
  private $courseId; 
  
  /* 
   * Creates a new game elements analytics renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
	}  
	
  /**
   * Renders the game elements analytics tab
   */
  public function render_tab() { 
		echo '<p>game_elements_analytics_renderer</p>';
	}
}
 ?>