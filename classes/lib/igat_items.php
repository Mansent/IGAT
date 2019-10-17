<?php
defined('MOODLE_INTERNAL') || die();


use block_stash\shortcodes;
use block_stash\manager;

/**
 * Library for managing items and trade
 */
class igat_items
{
  private $stashPluginInstalled;
  
  /**
   *  @return boolean if the stash plugin is installed
   */
	public function stashInstalled() {
    global $CFG, $DB;
    if(!isset($this->stashPluginInstalled)) {
      $records = $DB->get_records_sql("SHOW TABLES LIKE '" . $CFG->prefix . "block_stash'"); // check if table for stah exists in db
      $this->stashPluginInstalled = (count($records) > 0);
    }
    return $this->stashPluginInstalled;
  }
  
  /**
   * Outputs the user inventory for this course
   * @param int $courseId the id of the current course
   */
  public function getInventory($courseId) {
    global $PAGE;
    if(!$this->stashInstalled()) {
      return;
    }
    
    $manager = manager::get($courseId);
    
    $renderer = $PAGE->get_renderer('block_stash');
    $page = new \block_stash\output\block_content($manager);

    echo $renderer->render($page);
  }
  
  /**
   * Outputs all trades for this course
   * @param int $courseId the id of the current course
   */
  public function getTrade($courseId) {
    global $DB, $PAGE, $CFG;
    if(!$this->stashInstalled()) {
      return;
    }
    
    $records = $DB->get_records_sql("SELECT * FROM `" . $CFG->prefix . "block_stash` LEFT JOIN " . $CFG->prefix . "block_stash_trade 
                                      ON " . $CFG->prefix . "block_stash.id = " . $CFG->prefix . "block_stash_trade.stashid 
                                      WHERE courseid = " . $courseId);
    foreach($records as &$record) {
      $args = array();
      $args['secret'] =  $record->hashcode;
      echo shortcodes::trade(null, $args, null, $PAGE, null);
    }
  }
}