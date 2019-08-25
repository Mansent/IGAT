<?php
require_once("../../mod/alstea/classes/api/learningstylesapi.php");
require_once("../../mod/alstea/classes/api/datasetapi.php");

/**
 * Library for getting information about the students learning styles
 */
class igat_learningstyles {
  
  private $courseId;
  private $learningstylesapi;
  private $datasetapi;
  
  /**
   * Creates a new instance of this library
   */
  public function __construct($courseId) {
    $this->courseId = $courseId;
    $this->learningstylesapi = new mod_alstea\api\learningstylesapi();
    $this->datasetapi = new mod_alstea\api\datasetapi($this->learningstylesapi);
  }
  
  /**
   * Checks if the alstea plugin ist installed in moodle 
   * @return boolean if it is installed
   */
  public function lsPluginInstalled() {
    global $DB;
    $records = $DB->get_records_sql("SHOW TABLES LIKE 'mdl_alstea'"); // check if table for alstea exists
    return (count($records) > 0);
    
    //TODO also check this course
  }
  
  public function getUserScore($userId) {
    $datasetId = $this->getUserDatasetId($userId);
    return $this->datasetapi->get_scores_for_dataset($datasetId);
  }
  
  /**
   * Gets the id of an alstea dataset for the user
   * @param int $userId the id of the user for the dataset
   * @return int gets the id of the newest alstea datast for the given user
   */
  private function getUserDatasetId($userId) {
    global $DB;
    $record = $DB->get_record_sql("SELECT mdl_alstea_datasets.id FROM `mdl_course_modules` 
        INNER JOIN `mdl_modules` ON mdl_course_Modules.module = mdl_modules.id 
        INNER JOIN `mdl_alstea_datasets` ON mdl_course_modules.id = mdl_alstea_datasets.cmid 
      WHERE course = 2 AND userid = 3 AND name = 'alstea'  
      ORDER BY timecreated DESC");
    return $record->id;
  }
}

?>