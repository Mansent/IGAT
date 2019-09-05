<?php 
/**
 * Navigation for the Gamification Analytics Dashboard
 */

defined('MOODLE_INTERNAL') || die();

require_once("classes/renderer/game_elements_analytics_renderer.php");
require_once("classes/renderer/gamification_dashboard_analytics_renderer.php");
require_once("classes/renderer/analytics_config_renderer.php");
require_once('classes/lib/igat_capabilities.php');


// Check for rights
$lib_capabilities = new igat_capabilities();
if(!$lib_capabilities->isManagerOrTeacher($courseid, $USER->id)) {
  echo'<p>You must be manager or teacher to acess this page</p>';
  die();
}

// Check for curretn tab
$tab = $_GET['tab'];
if($tab != 'gameelements' && $tab != 'dashboard' && $tab != 'configuration') {
  $tab = 'dashboard';
}
$gameelementsClass = '';
$dashboardClass = '';
$configurationClass = '';
if($tab == 'gameelements') {
  $gameelementsClass = 'active';
}
else if($tab == 'dashboard') {
  $dashboardClass = 'active';
}
else if($tab == 'configuration') {
  $configurationClass = 'active';
}
?>
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php echo $dashboardClass; ?>" href="/blocks/igat/analytics.php?courseid=<?php echo $courseid; ?>&tab=dashboard	">Gamification Dashboard Analytics</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $gameelementsClass; ?>" href="/blocks/igat/analytics.php?courseid=<?php echo $courseid; ?>&tab=gameelements">Game Elements Analytics</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $configurationClass; ?>" href="/blocks/igat/analytics.php?courseid=<?php echo $courseid; ?>&tab=configuration">Configuration</a>
    </li>
</ul>
<?php
	if($tab == 'dashboard') {
		$gamification_dashboard_analytics_renderer = new gamification_dashboard_analytics_renderer($courseid);
		$gamification_dashboard_analytics_renderer->render_tab();
	}
	else if($tab == 'gameelements') {
		$game_elements_analytics_renderer = new game_elements_analytics_renderer($courseid);
		$game_elements_analytics_renderer->render_tab();
  }
  else if($tab == 'configuration') {
    $analytics_config_renderer = new analytics_config_renderer($courseid);
    $analytics_config_renderer->render_tab();
  }
?>