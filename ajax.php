<?php 
/**
 * This file provides the server backennd for ajax requests from the igat plugin.
 */ 
require_once('../../config.php');

global $DB, $OUTPUT, $PAGE, $USER;
if(!empty($_POST['loadtime']) && !empty($_POST['url']) && !empty($_POST['leavetime']) 
		&& !empty($_POST['destination']) && !empty($_POST['courseid'])) {
	$loadtime = $_POST['loadtime'];
	$url = $_POST['url'];
	$leavetime = $_POST['leavetime'];
	$destination = $_POST['destination'];
	$courseId = $_POST['courseid'];
	
	$tab = substr($url, strrpos($url, '=') + 1);
	if(!in_array($tab, ['progress', 'badges', 'ranks', 'settings'], true)) {
		die('invalid parameter');
	}
	$duration = $leavetime - $loadtime;
	$nextPage = parse_url($destination, PHP_URL_PATH) . '?' . parse_url($destination, PHP_URL_QUERY);
	
	$DB->insert_record('block_igat_dashboard_log', array('courseid' => $courseId,
																											 'userid' => $USER->id,
																											 'time' => $loadtime, 
																											 'duration' => $duration,
																											 'tab' => $tab,  
																											 'next_page' => $nextPage));
}

?>