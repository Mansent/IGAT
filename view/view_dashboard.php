<?php 
/**
 * This file contains the tabs for the igat dashboard
 */

require("classes/renderer/badges_renderer.php");
require("classes/renderer/progress_renderer.php");

$badges_renderer = new badges_renderer($courseid);
$progress_renderer = new progress_renderer($courseid);

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
        <a class="nav-link <?php echo $progressclass; ?>" href="#progress" data-toggle="tab" role="tab">Progress</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $badgesclass; ?>" href="#badges" data-toggle="tab" role="tab">Badges</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $ranksclass; ?>" href="#ranks" data-toggle="tab" role="tab">Ranks</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $settingsclass; ?>" href="#settings" data-toggle="tab" role="tab">Settings</a>
    </li>
</ul>
<div class="tab-content mt-3">
  <div class="tab-pane <?php echo $progressclass; ?>" id="progress" role="tabpanel">
	<?php $progress_renderer->render_tab(); ?>
  </div>
  <div class="tab-pane <?php echo $badgesclass; ?>" id="badges" role="tabpanel">
    <?php $badges_renderer->render_tab(); ?>
  </div>
  <div class="tab-pane <?php echo $ranksclass; ?>" id="ranks" role="tabpanel">
    <p>Ranks</p> 
  </div>  
  <div class="tab-pane <?php echo $settingsclass; ?>" id="settings" role="tabpanel">
    <p>Settings</p> 
  </div>  
</div>