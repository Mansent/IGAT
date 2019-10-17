<?php
defined('MOODLE_INTERNAL') || die();

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
		$ac_renderer->renderLsDateFilter(6); 
		$feedbackRate = $lib_statistics->getGamificationFeedbackRate();
		echo '<p>The students receive on average <b id="feedbackRate">' . $feedbackRate . '</b> gamification reinforcements per day they are active in the course.</p>';
		
		echo '<h3>Points distribution</h3>';
		$ac_renderer->renderLsFilter(7); 
		$histogram = $lib_statistics->getPointsDistribution();
		$labels = array_keys($histogram);
		$data = array_values($histogram);
		$ac_renderer->renderBarChart(7, $labels, $data, "Students", "Points Distribution", false); 
    
		echo '<h3>Levels distribution</h3>';
		$ac_renderer->renderLsFilter(8);
		$distribution = $lib_statistics->getLevelsDistribution();
		$labels = array_keys($distribution);
		$data = array_values($distribution);
		$ac_renderer->renderBarChart(8, $labels, $data, "Students", "Levels Distribution", false); 
    
    
		echo '<h3>Average days to reach level</h3>';
		$ac_renderer->renderLsFilter(9); 
    $daysdata = $lib_statistics->getAverageDaysToLevel();
		$labels = array_keys($daysdata);
		$data = array_values($daysdata);
		$ac_renderer->renderBarChart(9, $labels, $data, "Days", "Average days to reach level", false); 
    
    echo '<h3>Badges distribution</h3>';
    $ac_renderer->renderLsFilter(11);
    $distribution = $lib_statistics->getBadgesDistribution();
		$labels = array_keys($distribution);
		$data = array_values($distribution);
		$ac_renderer->renderBarChart(11, $labels, $data, "Students", "Badges Distribution", false); 
    
		echo '<h3>Average days to earn badge</h3>';
		$ac_renderer->renderLsFilter(10);
    $daysdata = $lib_statistics->getAverageDaysToBadges();
    $labels = array_keys($daysdata);
		$data = array_values($daysdata);
		$ac_renderer->renderBarChart(10, $labels, $data, "Days", "Average days to earn badges", false); 
	}
}
 ?>