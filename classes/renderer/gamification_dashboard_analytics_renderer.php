<?php
require_once('classes/renderer/analytics_components_renderer.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_progress.php');
/**
 * Responsible for gererating and rendering the gamification dashboard analytics 
 */
class gamification_dashboard_analytics_renderer 
{
  private $courseId; 
  
  /* 
   * Creates a new gamification dashboard analytics renderer renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
	}  
	
  /**
   * Renders the gamification dashboard analytics tab
   */
  public function render_tab() { 
		$ac_renderer = new analytics_components_renderer();
		$lib_statistics = new igat_statistics($this->courseId); //TODO no valid course id
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.js"></script>'; // include chart.js
		
		echo '<h3>Gamification page views</h3>';
		$ac_renderer->renderLsFilter(1); 
		/*$labels = array ("27.08.",	"28.08.",	"29.08.",	"30.08.",	"01.09.",	"02.09.");
		$progressData = array(10,	8,	7,	4,	8,	7);
		$badgesData = array(3,	4,	12,	9,	8,	4);
		$ranksData = array(2,	5,	3,	4,	6,	5);
		$settingsData = array(6,	7,	3,	4,	5,	8);*/
		$views = $lib_statistics->getDashboardPageViews();
		$ac_renderer->renderDashboardLineChart(1, $views->progress->labels, "Number of Views", $views->progress->data, $views->badges->data, $views->ranks->data, $views->settings->data);

		echo '<h3>Average page viewing time</h3>';
		$ac_renderer->renderLsFilter(2);
		$labels = array('Progress tab', 'Badges tab', 'Ranks tab', 'Settings tab');
		$data = array(3, 1, 5, 4);
		$ac_renderer->renderDashboardBarChart(2, $labels, $data, "Average viewing time (seonds)", "Viewing time"); 
		
		echo '<h3>Gamification dashboard subsequent pages</h3>';
		$ac_renderer->renderLsFilter(3); ?>
		<p>
			<span id="progressToBadges" class="edgeWeight">20%</span>
			<span id="progressToRanks" class="edgeWeight">20%</span>
			<span id="progressToSettings" class="edgeWeight">20%</span>
			<span id="badgesToProgress" class="edgeWeight">20%</span>
			<span id="badgesToRanks" class="edgeWeight">20%</span>
			<span id="badgesToSettings" class="edgeWeight">20%</span>
			<span id="ranksToProgress" class="edgeWeight">20%</span>
			<span id="ranksToBadges" class="edgeWeight">20%</span>
			<span id="ranksToSettings" class="edgeWeight">20%</span>
			<span id="settingsToProgress" class="edgeWeight">20%</span>
			<span id="settingsToBadges" class="edgeWeight">20%</span>
			<span id="settingsToRanks" class="edgeWeight">20%</span>
			<span id="progressToMoodle" class="edgeWeight">20%</span>
			<span id="badgesToMoodle" class="edgeWeight">20%</span>
			<span id="ranksToMoodle" class="edgeWeight">20%</span>
			<span id="settingsToMoodle" class="edgeWeight">20%</span>
			<span id="progressToExternal" class="edgeWeight">20%</span>
			<span id="badgesToExternal" class="edgeWeight">20%</span>
			<span id="ranksToExternal" class="edgeWeight">20%</span>
			<span id="settingsToExternal" class="edgeWeight">20%</span>
			<img src="/blocks/igat/img/graph.png" width="800" />
		</p>
<?php
		echo '<h3>Leaderboard visibility settings</h3>';
		$ac_renderer->renderLsFilter(4);
		$labels = array('Show full', 'Show limited', 'Hide');
		$data = array(12, 42, 33);
		$ac_renderer->renderDashboardBarChart(3, $labels, $data, "Number of Students", "Visibility Settings"); 
		
		echo '<h3>Leaderboard anonymity settings</h3>';
		$ac_renderer->renderLsFilter(5);
		$labels = array('Show full name', 'Anonymous');
		$data = array(40, 33);
		$ac_renderer->renderDashboardBarChart(4, $labels, $data, "Number of Students", "Anonymity Settings"); 
	}
}
 ?>