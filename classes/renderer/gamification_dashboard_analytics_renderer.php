<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/renderer/analytics_components_renderer.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_learningstyles.php');
require_once('classes/lib/igat_progress.php');
require_once('classes/lib/igat_teachersettings.php');
/**
 * Responsible for gererating and rendering the gamification dashboard analytics 
 */
class gamification_dashboard_analytics_renderer 
{
  private $courseId; 
  private $teachersettings;
  
  /* 
   * Creates a new gamification dashboard analytics renderer renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
    $this->teachersettings = new igat_teachersettings($courseId);
	}  
	
  /**
   * Renders the gamification dashboard analytics tab
   */
  public function render_tab() { 
    $tsettings = $this->teachersettings->getTeachersettings();
		$ac_renderer = new analytics_components_renderer($this->courseId);
		$lib_statistics = new igat_statistics($this->courseId);
    $lib_learningstyles = new igat_learningstyles($this->courseId);
    $lib_learningstyles->refreshLearningStyleData();
    
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js"></script>'; // include chart.js
		
		echo '<h3>Gamification page views</h3>';
		$ac_renderer->renderLsDateFilter(1, $tsettings->default_analytics_start, $tsettings->default_analytics_end); 
		$views = $lib_statistics->getDashboardPageViews(-11, 11, -11, 11, -11, 11, -11, 11, 
                                                    $tsettings->default_analytics_start, $tsettings->default_analytics_end);
		$ac_renderer->renderDashboardLineChart(1, $views->labels, "Number of Views", $views->progress, $views->badges, $views->ranks, $views->settings);

		echo '<h3>Average page viewing duration</h3>';
		$ac_renderer->renderLsDateFilter(2, $tsettings->default_analytics_start, $tsettings->default_analytics_end);
		$labels = array('Progress tab', 'Badges tab', 'Leaderboard tab', 'Settings tab');
		$durations = $lib_statistics->getAverageDashboardViewDurations(-11, 11, -11, 11, -11, 11, -11, 11, 
                                                                   $tsettings->default_analytics_start, $tsettings->default_analytics_end);
    $data = array($durations->progress, $durations->badges, $durations->ranks, $durations->settings);
		$ac_renderer->renderBarChart(2, $labels, $data, "Average viewing duration (seonds)", "Viewing duration", true); 
		
		echo '<h3>Gamification dashboard subsequent pages</h3>';
		$transitions = $lib_statistics->getSubsequentPagesStatistics(-11, 11, -11, 11, -11, 11, -11, 11, 
                                                                 $tsettings->default_analytics_start, $tsettings->default_analytics_end);
		$ac_renderer->renderLsDateFilter(3, $tsettings->default_analytics_start, $tsettings->default_analytics_end);
		$ac_renderer->renderSubsequentPagesGraph($transitions);
		
		echo '<h3>Leaderboard visibility settings</h3>';
		$ac_renderer->renderLsFilter(4);
		$labels = array('Show full', 'Show limited', 'Hide');
    $settingsData = $lib_statistics->getVisabilitySettingsStatistics();
		$data = array($settingsData->all, $settingsData->limited, $settingsData->hide);
		$ac_renderer->renderBarChart(4, $labels, $data, "Number of Students", "Visibility Settings", true); 
		
		echo '<h3>Leaderboard anonymity settings</h3>';
		$ac_renderer->renderLsFilter(5);
		$labels = array('Show full name', 'Anonymous');
    $settingsData = $lib_statistics->getAnonymitySettingsStatistics();
		$data = array($settingsData->show, $settingsData->hide);
		$ac_renderer->renderBarChart(5, $labels, $data, "Number of Students", "Anonymity Settings", true); 
	}
}
 ?>