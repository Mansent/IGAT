<?php 
/**
 * This file provides the server backennd for ajax requests from the igat plugin.
 */ 
require_once('../../config.php');
require_once('classes/lib/igat_logging.php');

global $DB, $OUTPUT, $PAGE, $USER;
$courseId = $_POST['courseid'];

// Logging gamification dashboard data
$lib_logging = new igat_logging();
if($lib_logging->loggingEnabledForUser($courseId, $USER->id)) {
	if(!empty($_POST['courseid']) && !empty($_POST['loadtime']) && !empty($_POST['url']) 
		&& !empty($_POST['leavetime']) && !empty($_POST['destination'])) {
		$loadtime = $_POST['loadtime'];
		$leavetime = $_POST['leavetime'];
		$url = $_POST['url'];
		$destination = $_POST['destination'];

		$lib_logging->logDashboardVisit($courseId, $USER->id, $loadtime, $leavetime, $url, $destination);
	}
}

?>