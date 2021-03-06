<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_statistics.php');
require_once($CFG->libdir . '/enrollib.php');

/**
 * Responsible for managing and rendering the badges tab in the gamification view 
 */
class badges_renderer {
	private $lib_badges;
	private $lib_statistics;
  private $lib_capabilities;
  private $courseId;
  
  /* 
   * Creates a new badges renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->lib_badges = new igat_badges($courseId);
		$this->lib_statistics = new igat_statistics($courseId);
    $this->lib_capabilities = new igat_capabilities();
    $this->courseId = $courseId;
	}
  
  /**
   * Renders the badges tab
   */
	public function render_tab() {
    global $USER;
    $badges = $this->lib_badges->getCurrentUserBadges();
    if($this->lib_capabilities->isManagerOrTeacher($this->courseId, $USER->id)) { ?>
      <h2 class="title">Student Badges</h2>
      <p style="padding-left: 20px;">The students will see a list of their obtained badges here.</p>
    <?php }
    else {
      echo '<h2 class="title">Your Badges</h2>';
      echo '<div class="igatbadgescontainer">';
      $i = 0;
      foreach($badges as &$badge) {
        if($badge->dateissued != null) { // user owns badge ?>
          <a href="<?php echo $this->lib_badges->getBadgePageUrl($badge); ?>" class="igatbadgelink">
            <div class="igatbadge igatbadgeowned">
              <img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" />
              <div class="igatbadgeinfo">
                <h3><b><?php echo $badge->name; ?></b></h3>
                <p><?php echo $badge->description; ?></p>
                <p style="font-size:12px">Earned on <?php echo userdate($badge->dateissued, '%d %b %Y'); ?></p>
                <p> 
                  <b><?php echo $this->lib_statistics->getBadgeAchievementRate($badge->id); ?>
                  of your class earned this badge</b>
                </p>
              </div>
            </div>
          </a>
  <?php		$i++;
        }
      }
      if($i == 0) {
        echo '<p>You have not earned any badges yet.</p>';
      }
      echo '</div>';
    }
    echo '<h2 class="title">Available Badges</h2>';
    echo '<div class="igatbadgescontainer">';
    $i = 0;
    foreach($badges as &$badge) {
      if($badge->dateissued == null) { // user has not yet achieved badge ?>
        <div class="igatbadge igatbadgeavailable">
          <img src="<?php echo $this->lib_badges->getBadgeImageUrl($badge); ?>" class="activatebadge" />
          <div class="igatbadgeinfo">
            <h3><b><?php echo $badge->name; ?></b></h3>
            <p><?php echo $badge->description; ?></p>
            <p> 
              <b><?php echo $this->lib_statistics->getBadgeAchievementRate($badge->id, $courseId); ?>
              of your class earned this badge</b>
            </p>
            <?php $this->render_criteria($badge); ?>
          </div>
        </div>		
<?php		$i++;
      }
    }	
    if($i == 0) {
      echo '<p>Currently there are no badges available.</p>';
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
			How to earn this badge?
		  </a>
		</p>
		<div class="collapse" id="collapseCriteria<?php echo $badge->id; ?>">
			<?php echo $core_renderer->print_badge_criteria($badge);  ?>
		</div>
<?php }
}
?>