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
		
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js"></script>'; // include chart.js
		
		echo '<h3>Gamification feedback rate</h3>';
		$ac_renderer->renderLsFilter(6); 
		$feedbackRate = $lib_statistics->getGamificationFeedbackRate();
		echo '<p>The students receive on average <b id="feedbackRate">' . $feedbackRate . '</b> gamification reinforcements per day they are active in this course.</p>';
		
		echo '<h3>Points distribution</h3>';
		$ac_renderer->renderLsFilter(7); 
		$histogram = $lib_statistics->getPointsDistribution();
		$labels = array_keys($histogram);
		$data = array_values($histogram);
		$ac_renderer->renderBarChart(7, $labels, $data, "Students", "Points Distribution", false); 
    
		echo '<h3>Levels distribution</h3>';
		$ac_renderer->renderLsFilter(8);
    
		echo '<h3>Average days to reach level</h3>';
		$ac_renderer->renderLsFilter(9);  ?>
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
      <label class="btn btn-secondary active">
        <input type="radio" name="options" id="option1" autocomplete="off" checked> Level 1
      </label>
      <label class="btn btn-secondary">
        <input type="radio" name="options" id="option2" autocomplete="off"> Level 2
      </label>
      <label class="btn btn-secondary">
        <input type="radio" name="options" id="option3" autocomplete="off"> Level 3
      </label>
    </div>
    <?php
    
		echo '<h3>Average days to earn badge</h3>';
		$ac_renderer->renderLsFilter(10);  ?>
    <div class="btn-group btn-group-toggle" data-toggle="buttons">
      <label class="btn btn-secondary active">
        <input type="radio" name="options" id="option1" autocomplete="off" checked> Badge 1
      </label>
      <label class="btn btn-secondary">
        <input type="radio" name="options" id="option2" autocomplete="off"> Badge 2
      </label>
      <label class="btn btn-secondary">
        <input type="radio" name="options" id="option3" autocomplete="off"> Badge 3
      </label>
    </div>
    <?php
	}
}
 ?>