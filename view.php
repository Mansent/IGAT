<?php
require_once('../../config.php');

global $DB, $OUTPUT, $PAGE;

// Check required parameters course id and login.
$courseid = required_param('courseid', PARAM_INT);
$tab = required_param('courseid', PARAM_ALPHA);

if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_igat', $courseid);
}
require_login($course);

// Page initialization
$PAGE->set_title("Your title");
$PAGE->set_url('/blocks/igat/index.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('blocktitle', 'block_igat'));

// Determine tab classes
$badgesclass = "";
$levelclass = "";
$ranksclass = "";
if($_GET['tab'] == 'badges') {
  $badgesclass = "active";
}
else if ($_GET['tab'] == 'level') {
  $levelclass = "active";
}
else if ($_GET['tab'] == 'ranks') {
  $ranksclass = "active";
}

// Gernerate page html
echo $OUTPUT->header(); ?>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php echo $badgesclass; ?>" href="#badges" data-toggle="tab" role="tab">Badges</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $levelclass; ?>" href="#level" data-toggle="tab" role="tab">Level</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $ranksclass; ?>" href="#ranks" data-toggle="tab" role="tab">Ranks</a>
    </li>
</ul>
<div class="tab-content mt-3">
  <div class="tab-pane <?php echo $badgesclass; ?>" id="badges" role="tabpanel">
    <p>Badges</p>
  </div>
  <div class="tab-pane <?php echo $levelclass; ?>" id="level" role="tabpanel">
    <p>Level</p>
  </div>
  <div class="tab-pane <?php echo $ranksclass; ?>" id="ranks" role="tabpanel">
    <p>Ranks</p> 
  </div>  
</div>
<?php echo $OUTPUT->footer(); ?>