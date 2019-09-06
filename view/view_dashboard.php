<?php 
/**
 * This file contains the tabs for the igat dashboard
 */
 
defined('MOODLE_INTERNAL') || die();

require_once("classes/renderer/badges_renderer.php");
require_once("classes/renderer/progress_renderer.php");
require_once("classes/renderer/ranks_renderer.php");
require_once("classes/renderer/usersettings_renderer.php");
require_once("classes/lib/igat_usersettings.php");
require_once("classes/lib/igat_capabilities.php");

$lib_usersettings = new igat_usersettings($courseid);
$usersettings = $lib_usersettings->getUsersettings($USER->id);

// Show teachers info
$lib_capabilities = new igat_capabilities();
if($lib_capabilities->isManagerOrTeacher($courseid, $USER->id)) { ?>
	<span class="notifications" id="user-notifications"><div class="alert alert-info alert-block fade in " role="alert" data-aria-autofocus="true" tabindex="0">
        <button type="button" class="close" data-dismiss="alert">×</button>
				Points and levels are deactivated for teachers.
    </div></span>
<?php }

// Determine tab classes for activating current tab
$badgesclass = "";
$progressclass = "";
$ranksclass = "";
$settingsclass = "";
if($_GET['tab'] == 'badges') {
  $badgesclass = "active";
}
else if ($_GET['tab'] == 'progress') {
  $progressclass = "active";
}
else if ($_GET['tab'] == 'ranks') {
  $ranksclass = "active";
}
else if ($_GET['tab'] == 'settings') {
  $settingsclass = "active";
}
?>

<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php echo $progressclass; ?>" href="/blocks/igat/dashboard.php?courseid=<?php echo $courseid; ?>&tab=progress">Progress</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $badgesclass; ?>" href="/blocks/igat/dashboard.php?courseid=<?php echo $courseid; ?>&tab=badges">Badges</a>
    </li>
<?php 
if( ($usersettings->leaderboarddisplay != 'hide'	&& !isset($_POST['leaderboarddisplay'])) // hide leaderboard tab if the user has disabled it
			|| (isset($_POST['leaderboarddisplay']) && $_POST['leaderboarddisplay'] != 'hide')) { ?>
    <li class="nav-item">
        <a class="nav-link <?php echo $ranksclass; ?>" href="/blocks/igat/dashboard.php?courseid=<?php echo $courseid; ?>&tab=ranks">Leaderboard</a>
    </li>
<?php } ?>
    <li class="nav-item">
        <a class="nav-link <?php echo $settingsclass; ?>" href="/blocks/igat/dashboard.php?courseid=<?php echo $courseid; ?>&tab=settings">Settings</a>
    </li>
</ul>
<div class="tab-content mt-3">
  <div class="tab-pane active" id="progress" role="tabpanel">
<?php if($_GET['tab'] == 'progress') {
				$progress_renderer = new progress_renderer($courseid);;
				$progress_renderer->render_tab();
			}
			else if ($_GET['tab'] == 'badges') {
				$badges_renderer = new badges_renderer($courseid);
				$badges_renderer->render_tab();
			}
			else if ($_GET['tab'] == 'ranks') {
				$ranks_renderer = new ranks_renderer($courseid);
				$ranks_renderer->render_tab();
			}
			else if ($_GET['tab'] == 'settings') {
				$usersettings_renderer = new usersettings_renderer($courseid);
				$usersettings_renderer->render_tab();
			} ?>
  </div> 
</div>

<?php // call js
$PAGE->requires->js_call_amd('block_igat/dashboard-logger', 'init'); 
?>