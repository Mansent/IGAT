<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/lib/igat_items.php');

/**
 * Responsible for managing and rendering the items tab in the gamification view 
 */
class items_renderer 
{
  private $courseId; 
  private $lib_items;
  
  /* 
   * Creates a new items renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
    $this->lib_items = new igat_items($courseId);
	}  
  
  /**
   * Renders the items tab
   */
  public function render_tab() { ?>
    <h2>You Inventory</h2>
<?php $this->lib_items->getInventory($this->courseId); ?>

    <h2>Trade</h2>
<?php $this->lib_items->getTrade($this->courseId);
  }
}
?>