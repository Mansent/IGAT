<?php 
/**
 * This file contains the tabs for the igat dashboard
 */

require("classes/renderer/badges_renderer.php");
require("classes/renderer/progress_renderer.php");

$badges_renderer = new badges_renderer();
$progress_renderer = new progress_renderer();

// Determine tab classes for activating current tab
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
?>

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
    <?php $badges_renderer->render_tab($courseid); ?>
  </div>
  <div class="tab-pane <?php echo $levelclass; ?>" id="level" role="tabpanel">
	<?php $progress_renderer->render_tab($courseid); ?>
  </div>
  <div class="tab-pane <?php echo $ranksclass; ?>" id="ranks" role="tabpanel">
    <p>Ranks</p> 
  </div>  
</div>