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
if(isset($_POST['graphid']) && isset($_POST['processingMin']) && isset($_POST['processingMax']) && isset($_POST['perceptionMin']) 
    && isset($_POST['perceptionMax']) && isset($_POST['inputMin']) && isset($_POST['inputMax']) && isset($_POST['understandingMin']) 
    && isset($_POST['understandingMax'])) {
      $graphId = $_POST['graphid'];
      $ac_renderer = new analytics_components_renderer($courseId);
      $lib_statistics = new igat_statistics($courseId);
      if($graphId == 1) { //Dashboard page views
        $views = $lib_statistics->getDashboardPageViews($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
        $ac_renderer->printJsonDashboardLineChartDatasets($views->progress, $views->badges, $views->ranks, $views->settings);
      }
      else if($graphId == 2) { //Dashboard page view durations 
        $durations = $lib_statistics->getAverageDashboardViewDurations($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
        $data = array($durations->progress, $durations->badges, $durations->ranks, $durations->settings);
        $ac_renderer->printJsonBarChartDataset("Viewing duration", $data, true);
      }
			else if($graphId == 3) { //Subsequent pages
				$transitions = $lib_statistics->getSubsequentPagesStatistics($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
				$ac_renderer->renderSubsequentPagesJson($transitions);
			}
      else if($graphId == 4) { //Chosen leaerboard display setting
        $displaySettings = $lib_statistics->getVisabilitySettingsStatistics($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
        $data = array($displaySettings->all, $displaySettings->limited, $displaySettings->hide);
        $ac_renderer->printJsonBarChartDataset("Visibility Settings", $data, true);
      }
      else if($graphId == 5) { //Chosen leaerboard anonymity setting
        $displaySettings = $lib_statistics->getAnonymitySettingsStatistics($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
        $data = array($displaySettings->show, $displaySettings->hide);
        $ac_renderer->printJsonBarChartDataset("Anonymity Settings", $data, true);
      }
      else if($graphId == 6) { //Gamification feedback rate
				$feedbackRate = $lib_statistics->getGamificationFeedbackRate($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
				echo $feedbackRate;
			}
			else if($graphId == 7) { //Points Distribution
				$histogram = $lib_statistics->getPointsDistribution($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
				$data = array_values($histogram);
        $ac_renderer->printJsonBarChartDataset("Points Distribution", $data, false);
			}
			else if($graphId == 8) { //Levels distribution
				$distribution = $lib_statistics->getLevelsDistribution($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
				$data = array_values($distribution);
        $ac_renderer->printJsonBarChartDataset("Levels Distribution", $data, false);
			}
			else if($graphId == 9) { //Average days to reach levels
				$distribution = $lib_statistics->getAverageDaysToLevel($_POST['processingMin'], $_POST['processingMax'],
                                                        $_POST['perceptionMin'], $_POST['perceptionMax'],
                                                        $_POST['inputMin'], $_POST['inputMax'],
                                                        $_POST['understandingMin'], $_POST['understandingMax']);
				$data = array_values($distribution);
        $ac_renderer->printJsonBarChartDataset("Average days to reach levels", $data, false);
			}
    }

?>