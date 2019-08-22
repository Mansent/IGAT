<?php
require_once('classes/lib/igat_ranks.php');
require_once('classes/lib/igat_badges.php');

/**
 * Responsible for managing and rendering the ranks tab in the gamification view 
 */
class ranks_renderer 
{
  private $courseId; 
  
  private $lib_ranks;
  private $lib_badges;
  
  /* 
   * Creates a new ranks renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
		$this->lib_ranks = new igat_ranks($courseId);
		$this->lib_badges = new igat_badges($courseId);
	}  
  
  /**
   * Renders the ranks tab
   */
  public function render_tab() {
    global $USER;
    $leaderboard = $this->lib_ranks->getLeaderboard(); ?>
    
    <table class="leaderboard">
      <tr>
        <th class="smallcolumn">Rank</th>
        <th class="mediumcolumn">Name</th>
        <th class="smallcolumn">Points</th>
        <th class="smallcolumn">Level</th>
        <th>Badges</th>
      </tr>
<?php $i = 1;
      foreach($leaderboard as &$leader) {
        $class = "";
        if($leader->userid == $USER->id) {
          $class = "curuser";
        }
        echo '<tr class="' . $class . '">';
        echo '<td class="smallcolumn">' . $i . '</td>';
        echo '<td class="mediumcolumn">' . $leader->firstname . ' ' . $leader->lastname . '</td>';
        echo '<td class="smallcolumn">' . $leader->xp . '</td>';
        echo '<td class="smallcolumn">' . $leader->lvl . '</td>';
        echo '<td>';
          foreach($leader->badges as &$badge) {
            $imgUrl = $this->lib_badges->getBadgeImageURL($badge);
            $linkUrl = $this->lib_badges->getBadgePageURL($badge);
            echo '<a href="' . $linkUrl . '">';
            echo '<img src="' . $imgUrl . '" class="badgepreview" width="50" />';
            echo '</a>';
          }
        echo '</td>';
        echo '</tr>';
        $i++;
      }
    ?> 
    </table>
<?php 
    if($i == 1) { ?>
      <span class="notifications" id="user-notifications"><div class="alert alert-info alert-block fade in " role="alert" data-aria-autofocus="true" tabindex="0">
          No players have earned any points.
      </div></span>
<?php
    }
  }
}
?>