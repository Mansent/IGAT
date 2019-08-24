<?php
require_once('classes/lib/igat_ranks.php');
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_usersettings.php');

/**
 * Responsible for managing and rendering the ranks tab in the gamification view 
 */
class ranks_renderer 
{
  private $courseId; 
  
  private $lib_ranks;
  private $lib_badges;
  private $lib_usersettings;
  
  /* 
   * Creates a new ranks renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
		$this->lib_ranks = new igat_ranks($courseId);
		$this->lib_badges = new igat_badges($courseId);
		$this->lib_usersettings = new igat_usersettings($courseId);
	}  
  
  /**
   * Renders the ranks tab
   */
  public function render_tab() {
    global $USER;
		
		$usersettings = $this->lib_usersettings->getUsersettings($USER->id);
		var_dump($usersettings);
		
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
						if(!$leader->anonymous) {
							echo '<a href="' . $this->lib_badges->getBadgePageURL($badge) . '">';
						}
            echo '<img src="' . $this->lib_badges->getBadgeImageURL($badge) . '" class="badgepreview" width="50" />';
            if(!$leader->anonymous) {
							echo '</a>';
						}
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