<?php 
/**
 * This file provides the server backennd for ajax requests from the igat plugin.
 */ 
require_once('../../config.php');
require_once('classes/lib/igat_logging.php');
require_once('classes/lib/igat_capabilities.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_progress.php');
require_once('classes/renderer/analytics_components_renderer.php');

global $DB, $OUTPUT, $PAGE, $USER;
$courseId = $_POST['courseid'];
$lib_capabilities = new igat_capabilities();

// Logging gamification dashboard data
if(!empty($_POST['courseid']) && !empty($_POST['loadtime']) && !empty($_POST['url']) 
    && !empty($_POST['leavetime']) && !empty($_POST['destination'])) {
  if($lib_capabilities->isStudent($courseId, $USER->id)) {			
		$lib_logging = new igat_logging();
		$loadtime = $_POST['loadtime'];
		$leavetime = $_POST['leavetime'];
		$url = $_POST['url'];
		$destination = $_POST['destination'];

		$lib_logging->logDashboardVisit($courseId, $USER->id, $loadtime, $leavetime, $url, $destination);
	}
}

// Gamification Analytics Request
if(isset($_POST['processingMin']) && isset($_POST['processingMax']) && isset($_POST['perceptionMin']) && isset($_POST['perceptionMax'])
    && isset($_POST['inputMin']) && isset($_POST['inputMax']) && isset($_POST['understandingMin']) && isset($_POST['understandingMax'])) {
      
      //Dashboard page views
      $ac_renderer = new analytics_components_renderer($courseId);
      $lib_statistics = new igat_statistics($courseId);
      $views = $lib_statistics->getDashboardPageViews($_POST['processingMin'], $_POST['processingMax'],
                                                      $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                      $_POST['inputMin'], $_POST['inputMax'],
                                                      $_POST['understandingMin'], $_POST['understandingMax']);
      $ac_renderer->printJsonDashboardLineChartDatasets($views->progress, $views->badges, $views->ranks, $views->settings);

    }

?>