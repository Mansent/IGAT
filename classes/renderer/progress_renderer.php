<?php

/**
 * Responsible for managing and rendering the levels tab in the gamification view 
 */
class progress_renderer {
  
  /**
   * Renders the levels tab
   * @param courseid the id of the current course.
   */
  public function render_tab($courseid) {
    global $DB, $USER;
    
    $userinfo = $DB->get_record('block_xp', array('courseid' => $courseid, 'userid' => $USER->id)); //SQL query	?>
	
<!--	<h3>Level: <?php echo $userinfo->lvl; ?></h3>
	<h4>Points: <?php echo $userinfo->xp; ?></h4> -->
	
	<h2>Your Progress</h1>
	<div class="progressflex">
		<div class="progressquickinfo">
			<img width="32" height="32" src="/blocks/igat/img/achievement.png"/>
			2 / 10 badges
		</div>
		<div class="progressquickinfo">
			<img width="32" height="32" src="/blocks/igat/img/star.png"/>
			6 / 10 levels
		</div>
	</div>
	<div class="progress">
	  <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	
	<hr />
	
	<h2>Your Points</h2>
	<div class="progressflex">
		<div class="progressblock">
			<h6>Level</h6>
			<img width="100" height="100" src="/blocks/igat/img/level.png"/>
			<span class="leveloverlay">4</span>
		</div>
		<div class="progressblock">
			<h6>Points</h6>
			<span class="progressinfo"><b>20</b></span>
			
			<h6>Points till next level</h6>
			<span class="progressinfo"><b>67</b></span>
			
			<div class="progress">
			  <div class="progress-bar" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
		</div>
		<div class="progressblock">
			<h6>Statistics</h6>
			<span class="progressinfo"><b>37%</b> are in your level</span>
			<span class="progressinfo"><b>13%</b> are in a higher level</span>
			<span class="progressinfo"><b>50%</b> are in a lower level</span>
			<a href="#ranks">View ranks</a>	
		</div>
	</div>
	
	<hr />
	
	<h2>Your badges</h2>
	<div class="progressflex">
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
		<div class="badgepreview">
			<img src="http://127.0.0.1/pluginfile.php/25/badges/badgeimage/1/f2" class="activatebadge">
		</div>
	</div>
	<a href="#badges">View all badges</a>
	
	<hr />
	
	<h2>Learning Styles</h2>

<?php
  }
}
?>