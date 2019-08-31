<?php 
/**
 * This file provides the server backennd for ajax requests from the igat plugin.
 */ 
require_once('../../config.php');
require_once('classes/lib/igat_logging.php');
require_once('classes/lib/igat_capabilities.php');

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
      echo 'Analytics Request!';
    }

?>