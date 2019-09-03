<?php 
	require_once("classes/renderer/game_elements_analytics_renderer.php");
	require_once("classes/renderer/gamification_dashboard_analytics_renderer.php");


	$tab = $_GET['tab'];
	if($tab != 'gameelements' && $tab != 'dashboard') {
		$tab = 'dashboard';
	}
	$gameelementsClass = '';
	$dashboardClass = '';
	if($tab == 'gameelements') {
		$gameelementsClass = 'active';
	}
	else if($tab == 'dashboard') {
		$dashboardClass = 'active';
	}
?>
<ul class="nav nav-tabs" role="tablist">
    <li class="nav-item">
        <a class="nav-link <?php echo $dashboardClass; ?>" href="/blocks/igat/analytics.php?courseid=<?php echo $courseid; ?>&tab=dashboard	">Gamification Dashboard Analytics</a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?php echo $gameelementsClass; ?>" href="/blocks/igat/analytics.php?courseid=<?php echo $courseid; ?>&tab=gameelements">Game Elements Analytics</a>
    </li>
</ul>
<?php
	if($tab == 'dashboard') {
		$gamification_dashboard_analytics_renderer = new gamification_dashboard_analytics_renderer($courseid);
		$gamification_dashboard_analytics_renderer->render_tab();
	}
	if($tab == 'gameelements') {
		$game_elements_analytics_renderer = new game_elements_analytics_renderer($courseid);
		$game_elements_analytics_renderer->render_tab();
  }
?>
		<div class="tab-content mt-3">
			<div class="tab-pane active" id="progress" role="tabpanel">
			</div>
		</div>