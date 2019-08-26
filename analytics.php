<?php
/**
 * This file contains the content of the igat analytics page
 */
  
require_once('../../config.php');

global $DB, $OUTPUT, $PAGE, $USER;

// Check required parameters course id and login.
$courseid = required_param('courseid', PARAM_INT);
$tab = required_param('courseid', PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_igat', $courseid);
}
require_login($course);

// Page initialization
$PAGE->set_title("Gamification Analytics");
$PAGE->set_url('/blocks/igat/analytics.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading("Gamification Analytics");

// Gernerate page html
echo $OUTPUT->header(); 

include('view/view_analytics.php');

echo $OUTPUT->footer(); ?>