<?php
require_once('classes/lib/igat_progress.php');
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_statistics.php');

/**
 * Responsible for managing and rendering the levels tab in the gamification view 
 */
class progress_renderer 
{
  private $courseId; 
  
  private $lib_progress;
	private $lib_badges;
	private $lib_statistics;
  
  /* 
   * Creates a new progress renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
    $this->lib_progress = new igat_progress($courseId);
		$this->lib_badges = new igat_badges($courseId);
		$this->lib_statistics = new igat_statistics($courseId);
	}  
  
  /**
   * Renders the levels tab
   */
  public function render_tab() {
    global $USER;
    
    //number of badges
    $numUserBadges = $this->lib_badges->getNumUserBadges();
    $numAvailableBadges = $this->lib_badges->getNumAvailableBadges();
    
    //user points and level
    $userInfo = $this->lib_progress->getCurrentUserInfo();
    $userPoints = $userInfo->xp;
    
    //user level progress
    $userLevel = $userInfo->lvl;
    $maxLevel = $this->lib_progress->getNumLevels();
    $pointsToNextLevel = $this->lib_progress->getPointsToNextLevel($USER->id);
    $levelProgress = $this->lib_progress->getCurrentLevelProgress($USER->id) * 100;
    
    //overall user progress
    $progress = (($numUserBadges + $userLevel) / ($numAvailableBadges +  $maxLevel)) * 100;
    
    //achieved badges
		$badges = $this->lib_badges->getCurrentUserBadges();
    ?>
	
	<h2>Your Progress</h1>
	<div class="progressflex">
		<div class="progressquickinfo">
			<img width="32" height="32" src="/blocks/igat/img/achievement.png"/>
			<?php echo $numUserBadges; ?> / <?php echo $numAvailableBadges; ?> badges
		</div>
		<div class="progressquickinfo">
			<img width="32" height="32" src="/blocks/igat/img/star.png"/>
			<?php echo $userLevel; ?> / <?php echo $maxLevel; ?> levels
		</div>
	</div>
	<div class="progress">
	  <div class="progress-bar" role="progressbar" style="width: <?php echo $progress ; ?>%" aria-valuenow="<?php echo $progress ; ?>" aria-valuemin="0" aria-valuemax="100"></div>
	</div>
	
	<hr />
	
	<h2>Your Points</h2>
	<div class="progressflex">
		<div class="progressblock">
			<h6>Level</h6>
			<img width="100" height="100" src="/blocks/igat/img/level.png"/>
			<span class="leveloverlay"><?php echo $userLevel; ?></span>
		</div>
		<div class="progressblock">
			<h6>Points</h6>
			<span class="progressinfo"><b><?php echo $userPoints; ?></b></span>
			
			<h6>Points to next level</h6>
			<span class="progressinfo"><b><?php echo $pointsToNextLevel; ?></b></span>
			
			<div class="progress">
			  <div class="progress-bar" role="progressbar" style="width: <?php echo $levelProgress; ?>%" aria-valuenow="<?php echo $levelProgress; ?>" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
		</div>
		<div class="progressblock">
			<h6>Statistics</h6>
			<span class="progressinfo"><b>37%</b> are in your level</span>
			<span class="progressinfo"><b>13%</b> are in a higher level</span>
			<span class="progressinfo"><b>50%</b> are in a lower level</span>
			<a href="<?php echo new moodle_url('/blocks/igat/view.php', array('courseid' => $this->courseId, 'tab' => 'ranks')); ?>">View Ranks</a>	
		</div>
	</div>
	
	<hr />
	
	<h2>Your Badges</h2>
	<div class="progressflex">
<?php 
  $ownsBadges = false;
  foreach($badges as &$badge) {
			if($badge->dateissued != null) { // user owns badge 
        $ownsBadges = true; ?>
        <div class="badgepreview">
          <a href="<?php echo new moodle_url('/blocks/igat/view.php', array('courseid' => $this->courseId, 'tab' => 'badges')); ?>">
            <img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" width="70" />
          </a>
        </div>
<?php } 
  } 
  if(!$ownsBadges) { ?>
    <p>You haven't received a badge yet.</p>
<?php } ?>
	</div>
	<a href="<?php echo new moodle_url('/blocks/igat/view.php', array('courseid' => $this->courseId, 'tab' => 'badges')); ?>">View all Badges</a>
	
	<hr />
	
	<h2>Learning Styles</h2>

<?php
  }
}
?>