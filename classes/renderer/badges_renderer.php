<?php
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_statistics.php');
require_once($CFG->libdir . '/enrollib.php');


/**
 * Responsible for managing and rendering the badges tab in the gamification view 
 */
class badges_renderer {
	private $lib_badges;
	private $lib_statistics;
  
	public function __construct() {
		$this->lib_badges = new igat_badges();
		$this->lib_statistics = new igat_statistics();
	}
  
  /**
   * Renders the badges tab
   * @param courseId the id of the current course.
   */
	public function render_tab($courseId) {
		$badges = $this->lib_badges->getCurrentUserBadges($courseId);
		
		echo '<h2>Your Badges</h2>';
		echo '<div class="igatbadgescontainer">';
		foreach($badges as &$badge) {
			if($badge->dateissued != null) { // user owns badge ?>
				<a href="/badges/badge.php?hash=<?php echo $badge->uniquehash; ?>">
					<div class="igatbadge igatbadgeowned">
						<img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" />
						<div class="igatbadgeinfo">
							<h3><?php echo $badge->name; ?></h3>
							<p><?php echo $badge->description; ?></p>
							<p>Earned on <?php echo userdate($badge->dateissued, '%d %b %Y'); ?></p>
							<p> 
								<?php echo $this->lib_statistics->getBadgeAchievementRate($badge->id, $courseId); ?>
								of your class earned this badge
							</p>
						</div>
					</div>
				</a>
<?php		}
		}
		echo '</div>';
		
		echo '<h2>Available Badges</h2>';
		echo '<div class="igatbadgescontainer">';
		foreach($badges as &$badge) {
			if($badge->dateissued == null) { // user has not yet achieved badge ?>
				<div class="igatbadge igatbadgeavailable">
					<img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" />
					<div class="igatbadgeinfo">
						<h3><?php echo $badge->name; ?></h3>
						<p><?php echo $badge->description; ?></p>
						<p> 
							<?php echo $this->lib_statistics->getBadgeAchievementRate($badge->id, $courseId); ?>
							of your class earned this badge
						</p>
						<?php $this->render_criteria($badge); ?>
					</div>
				</div>				
<?php		}
		}
		echo '</div>';
	}
	
	/**
	 * Renders the criteria for earning a badge
	 * @param badge $badge the badge to render the criteria for
	 */
	public function render_criteria(badge $badge) {
		$core_renderer = $this->lib_badges->getCoreRenderer(); ?>		
		<p class="collapseContainer">
		  <a data-toggle="collapse" href="#collapseCriteria<?php echo $badge->id; ?>" role="button" aria-expanded="false" aria-controls="collapseExample">
			Earn this badge
		  </a>
		</p>
		<div class="collapse" id="collapseCriteria<?php echo $badge->id; ?>">
			<?php echo $core_renderer->print_badge_criteria($badge);  ?>
		</div>
<?php }
}
?>