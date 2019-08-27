<?php
require_once('classes/lib/igat_progress.php');
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_learningstyles.php');

/**
 * Responsible for managing and rendering the levels tab in the gamification view 
 */
class progress_renderer 
{
  private $courseId; 
  
  private $lib_progress;
	private $lib_badges;
	private $lib_statistics;
	private $lib_learningstyles;
  
  /* 
   * Creates a new progress renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
    $this->lib_progress = new igat_progress($courseId);
		$this->lib_badges = new igat_badges($courseId);
		$this->lib_statistics = new igat_statistics($courseId);
    $this->lib_learningstyles = new igat_learningstyles($courseId);
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
    
    //user level progress statistics
    $levelProgressStatistics = $this->lib_statistics->getUserLevelStatistics($USER->id);
    
		//open user activities to earn points
		$openActivities = $this->lib_progress->getOpenActivities($USER->id);
		
    //overall user progress
    $progress = (($numUserBadges + $userLevel) / ($numAvailableBadges +  $maxLevel)) * 100;
    
    //achieved badges
		$badges = $this->lib_badges->getCurrentUserBadges();
    
    if($maxLevel == 0) { ?>
    <span class="notifications" id="user-notifications"><div class="alert alert-info alert-block fade in " role="alert" data-aria-autofocus="true" tabindex="0">
        <button type="button" class="close" data-dismiss="alert">×</button>
<?php     if($userPoints < 0) {
            echo 'The teacher has to configure the levels in the Level Up plugin';
          }
          else {
            echo 'Gamification has not yet been setup by the teacher in the Level up plugin.';
          } ?>
    </div></span>
    <?php
    }
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
		<div class="progressblock" id="levelprogressblock">
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
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->equal * 100; ?>%</b> are in your level</span>
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->higher * 100; ?>%</b> are in a higher level</span>
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->lower * 100; ?>%</b> are in a lower level</span>
			<a href="<?php echo new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $this->courseId, 'tab' => 'ranks')); ?>">View Ranks</a>	
		</div>
		<div class="progressblock">
			<h6>Earn Points</h6>
			<?php foreach($openActivities as &$info) {
				echo '<span class="progressinfo">' . $info . '</span>';
			} 
			if(count($openActivities) == 0) {
				echo '<p>You completed all quizzes and assignments!</p>';
			}?>
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
          <a href="<?php echo new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $this->courseId, 'tab' => 'badges')); ?>">
            <img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" width="70" />
          </a>
        </div>
<?php } 
  } 
  if(!$ownsBadges) { ?>
    <p>You haven't received a badge yet.</p>
<?php } ?>
	</div>
	<a href="<?php echo new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $this->courseId, 'tab' => 'badges')); ?>">View all Badges</a>
	
	<hr />
	
	<h2>Learning Styles</h2>
<?php
	  if($this->lib_learningstyles->lsPluginInstalled()) {
			$learningStyleSummary = $this->lib_learningstyles->getUserSummary($USER->id);
			if($learningStyleSummary === false) {
				echo '<p>You have not taken the learning style questionnaire yet. Take the questionnaire to get in-deph information and recommendations for your learning style</p>';
			}
			else {
				foreach($learningStyleSummary as &$summary) {
					echo '<p>' . $summary . '</p>';
				}
			}
		}
		else {
			echo '<p>The learning styles plugin is not installed.</p>';
		}
  }
}
?>