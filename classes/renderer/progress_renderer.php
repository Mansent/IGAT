<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/lib/igat_progress.php');
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_statistics.php');
require_once('classes/lib/igat_learningstyles.php');
require_once('classes/lib/igat_capabilities.php');

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
  private $lib_capabilities;
  
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
    $this->lib_capabilities = new igat_capabilities();
	}  
  
  /**
   * Renders the levels tab
   */
  public function render_tab() {
    global $USER;
    
    // This tab does not contain any information for teachers
    if($this->lib_capabilities->isManagerOrTeacher($this->courseId, $USER->id)) { ?>
        <h1>Student Progress</h1>
        <p>Students will see an overview of their current score, their level and available learning activities for earning points on this page.</p>
        <?php 
    }
    else { // Current user is student -> render the progress tab
    
    //number of badges
    $numUserBadges = $this->lib_badges->getNumUserBadges();
    $numAvailableBadges = $this->lib_badges->getNumAvailableBadges();
    
    //user points and level
    $userInfo = $this->lib_progress->getCurrentUserInfo();
    $userPoints = $userInfo->xp;
    
    //user level and progress
    $userLevel = $userInfo->lvl;
    $maxLevel = $this->lib_progress->getNumLevels();
    $pointsToNextLevel = $this->lib_progress->getPointsToNextLevel($USER->id);
		$levelProgress = $this->lib_progress->getCurrentLevelProgress($USER->id) * 100;
    
    $currentLevelImage = $this->lib_progress->getCurrentUserLevelImage();
    $levelsInfo = $this->lib_progress->getFullLevelsInfo();
    $levelName = $levelsInfo['name'][$userLevel];
    $levelDesc = $levelsInfo['desc'][$userLevel];
    
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
	
	<h2>Your Points and Levels</h2>
	<div class="progressflex">
		<div class="progressblock firstblock" id="levelprogressblock">
			<div class="left"><h5><b><?php echo $levelName; ?></b></h5>
			<img class="levelimg" width="100" height="100" src="<?php echo $currentLevelImage; ?>"/></div>
      <?php if ($this->lib_progress->hasLevelUpPlus()) {?>
        <div class="leveldesc">
        
          <span class="progressinfo"><?php echo $levelDesc; ?></span>
					<p class="progressinfo1">
						<a data-toggle="collapse" href="#collapseLevels" role="button" aria-expanded="true" aria-controls="collapseExample" id="yui_3_17_2_1_1571318208590_21" class="">
							Show all available levels
						</a>
					</p>
        </div>
				<div class="collapse" id="collapseLevels" style="">
							<?php $this->renderAllLevels(); ?>
				</div>
      <?php } 
      else { // render the current level above the level star ?>
        <span class="leveloverlay"><?php echo $userLevel; ?></span>
      <?php } ?>
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
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->equal * 100; ?>%</b> of your peers are in your level</span>
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->higher * 100; ?>%</b> of your peers are in a higher level</span>
			<span class="progressinfo"><b><?php echo $levelProgressStatistics->lower * 100; ?>%</b> of your peers are in a lower level</span>
			<a class="leaderlink" href="<?php echo new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $this->courseId, 'tab' => 'ranks')); ?>">View Leaderboard</a>	
		</div>
		<div class="progressblock">
			<h6>Earn Points</h6>
			<?php foreach($openActivities as &$info) {
				echo '<span class="progressinfo">' . $info . '</span>';
			} 
			if(count($openActivities) == 0) {
				echo '<p>There are no open assignments or quizzes with a point reward that you have not completed yet.</p>';
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
	
	
    <?php }
  }
	
	/**
	 * Renders an overview of all available levels
	 */
	private function renderAllLevels() { ?>
			<table class="leaderboard">
				<tr>
					<th>Level</th>
					<th>Image</th>
					<th>Name</th>
					<th>Desctiption</th>
					<th>Points required</th>
				</tr>
<?php		$levelsInfo = $this->lib_progress->getFullLevelsInfo();
				$levelsImages = $this->lib_progress->getLevelsImages();
				for($i=1; $i <= count($levelsInfo['xp']); $i++) {
					//load level info
					$name = '';
					$description = '';
					$pointsReq = $levelsInfo['xp'][$i];
					$image = $levelsImages[$i];
					if(isset($levelsInfo['name'][$i])) {
						$name = $levelsInfo['name'][$i];
					}
					if(isset($levelsInfo['desc'][$i])) {
						$description = $levelsInfo['desc'][$i];
					}
					echo '<tr>';
					echo '<td>' . $i . '</td>';
					echo '<td><img src="' . $image . '" width="50" height="50" /></td>';
					echo '<td>' . $name . '</td>';
					echo '<td>' . $description . '</td>';
					echo '<td>' . $pointsReq . '</td>';
					echo '</tr>';
				}
?>
			</table>
<?php	}
} 
?>