<?php 
/**
 * This file provides the server backennd for ajax requests from the igat plugin.
 */ 
require_once('../../config.php');
require_once('classes/lib/igat_logging.php');

global $DB, $OUTPUT, $PAGE, $USER;
if(!empty($_POST['courseid']) && !empty($_POST['loadtime']) && !empty($_POST['url']) 
		&& !empty($_POST['leavetime']) && !empty($_POST['destination'])) {
			
	$courseId = $_POST['courseid'];
	$loadtime = $_POST['loadtime'];
	$leavetime = $_POST['leavetime'];
	$url = $_POST['url'];
	$destination = $_POST['destination'];

	$lib_logging = new igat_logging();
	$lib_logging->logDashboardVisit($courseId, $USER->id, $loadtime, $leavetime, $url, $destination);
}

?>