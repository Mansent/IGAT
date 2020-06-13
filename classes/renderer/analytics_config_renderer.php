<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/lib/igat_logging.php');
/**
 * Renders the gamification analytics configuration tab 
 */
class analytics_config_renderer 
{
  private $courseId; 
  private $lib_logging;
  
  /* 
   * Creates a new gamification dashboard analytics renderer renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
    $this->lib_logging = new igat_logging();
	}  
	
  /**
   * Renders the gamification dashboard analytics tab
   */
  public function render_tab() { 
    // evaluate delete form
    $res = 'nodelete';
    /* Remove deleting fuction to avoid abuse in public demo
    if(!empty($_POST['deleteBefore'])) {
      $date = $_POST['deleteBefore'];
      $res = $this->lib_logging->deleteLogsBefore($date, $this->courseId);
    }
    if(!empty($_POST['deleteAfter'])) {
      $date = $_POST['deleteAfter'];
      $res = $this->lib_logging->deleteLogsAfter($date, $this->courseId);
    }*/
    if($res !== 'nodelete') { ?>
    <span class="notifications" id="user-notifications"><div class="alert alert-info alert-block fade in " role="alert" data-aria-autofocus="true" tabindex="0">
        <button type="button" class="close" data-dismiss="alert">×</button>
<?php     if($res) {
            echo 'The data has been deleted.';
          }
          else {
            echo 'The specified date is not valid.';
          } ?>
    </div></span>
    <?php
    }
  ?>
  <h3>Configure Gamification</h3>
  
  <ul>
    <li><a href="/badges/index.php?type=2&id=<?php echo $this->courseId; ?>">Manage Badges</a></li>
    <li><a href="/blocks/xp/index.php/rules/<?php echo $this->courseId; ?>">Configure rules to earn points</a></li>
    <li><a href="/blocks/xp/index.php/levels/<?php echo $this->courseId; ?>">Configure levels</a></li>
    <li><a href="/blocks/xp/index.php/config/<?php echo $this->courseId; ?>">Level Up! plugin settings</a></li>
    <li><a href="/blocks/stash/items.php?courseid=<?php echo $this->courseId; ?>">Stash items and trade configuration</a></li>
  </ul>

<?php /* Remove deleting fuction to avoid abuse in public demo 
  <h3>Delete all logs and data</h3>
  <form method="post">
    <p>
      <label>Delete all data before</label> 
      <input type="text" name="deleteBefore" value="2019-01-05" /> 
      <input type="submit" name="submitBefore" value="Delete data"/>
    </p>
  </form>
  <form method="post">
    <p>
      <label>Delete all data after</label>
      <input type="text" name="deleteAfter" value="2020-09-05" /> 
      <input type="submit" name="submitAfter" value="Delete data"/>
    </p>
  </form>
<?php	*/
  }
}
 ?>