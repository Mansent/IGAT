<?php
require_once('classes/renderer/analytics_components_renderer.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_learningstyles.php');
require_once('classes/lib/igat_progress.php');

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
		$ac_renderer = new analytics_components_renderer($this->courseId);
		$lib_statistics = new igat_statistics($this->courseId);
		
		echo '<h3>Gamification feedback rate</h3>';
		$ac_renderer->renderLsFilter(6); 
		$feedbackRate = $lib_statistics->getGamificationFeedbackRate();
		echo '<p>The student receive on average <b id="feedbackRate">' . $feedbackRate . '</b> feedbacks per day.</p>';
		
		echo '<h3>Points distribution</h3>';
		echo '<h3>Levels distribution</h3>';
		echo '<h3>Average days to reach level</h3>';
		echo '<h3>Average days to earn badge</h3>';
	}
}
 ?>