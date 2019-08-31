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
  private $pluginInstalled;
  
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
    
    if(isset($pluginInstalled)) {
      return $pluginInstalled;
    }
    
    $records = $DB->get_records_sql("SHOW TABLES LIKE 'mdl_alstea'"); // check if table for alstea exists
    $dbOk = (count($records) > 0);
    
    if($dbOk) {
      $records = $DB->get_records_sql("SELECT * FROM `mdl_course_modules` 
          INNER JOIN `mdl_modules` ON mdl_course_Modules.module = mdl_modules.id 
        WHERE name = 'alstea'");
      $courseOk = (count($records) > 0);
      $this->pluginInstalled = $courseOk;
      return $courseOk;
    }
    $this->pluginInstalled = $courseOk;
    return false;
  }
  
  /**
   * Gets the learning style score for a user
   * param int $userId the user to load the learning styles score for
   * @return the learning style score for each dimension or false if the user did not take the questionnaire
   */
  public function getUserScore($userId) {
    if(!$this->lsPluginInstalled()) {
      return 0;
    }
    $datasetId = $this->getUserDatasetId($userId);
    if($datasetId == null) {
      return 0;
    }
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
      WHERE course = " . $this->courseId . " AND userid = " . $userId . " AND name = 'alstea'  
      ORDER BY timecreated DESC");
    return $record->id;
  }
  
  /**
   * Calculates all learning style score from the ALSTEA questionnaire data and
   * stores them in the block_igat_learningstyles table. This enabels other functions
   * to perform joins in sql queries and filter results by learning style.
   */
  public function refreshLearningStyleData() {
    global $DB;
    $DB->delete_records_select('block_igat_learningstyles', 'TRUE');
    // get available datasets with user ids from db
    $sql = "SELECT mdl_alstea_datasets.id, userid FROM `mdl_course_modules` 
              INNER JOIN `mdl_modules` ON mdl_course_Modules.module = mdl_modules.id 
              INNER JOIN `mdl_alstea_datasets` ON mdl_course_modules.id = mdl_alstea_datasets.cmid 
            WHERE course = " . $this->courseId . " AND name = 'alstea'  
            ORDER BY timecreated DESC";
    $records = $DB->get_records_sql($sql);
    
    //calculate learning style questionnaire results
    $insertRecords = array();
    foreach($records as &$record) {
      $score = $this->datasetapi->get_scores_for_dataset($record->id);
      
      $insertRecord = new stdClass();
      $insertRecord->processing = $score['reflective'] - $score['active'];
      $insertRecord->perception = $score['intuitive'] - $score['sensing'];
      $insertRecord->input = $score['verbal'] - $score['visual'];
      $insertRecord->comprehension = $score['global'] - $score['sequential'];
      $insertRecord->userid = $record->userid;
      $insertRecord->courseid = $this->courseId;
      array_push($insertRecords, $insertRecord);
    }
    
    $DB->insert_records('block_igat_learningstyles', $insertRecords);
  }
  
  /**
   * Gets a summary of the users learning preferences 
   * @param int $userId the id of the user to get the summary for
   * @return array an array with an information text for each learning style dimension 
   *          or false if the user has not taken the ls questionnaire yet
   */
  public function getUserSummary($userId) {
    $dataset = $this->getUserDatasetId($userId);
    if($dataset === null) {
      return false;
    }
    $scores = $this->datasetapi->get_scores_for_dataset($dataset);

    $learningstyles = $this->learningstylesapi->get_learning_styles();

    $data = array_map(function (string $dimension) use ($learningstyles, $scores): string {
      $styles = $learningstyles[$dimension];

      $dimensionscores = array_map(function (string $style) use ($scores): int {
        return (int) $scores[$style];
      }, $styles);

      $difference = abs($dimensionscores[0] - $dimensionscores[1]);

      //get category
      if ($difference <= 3) {
        $category = 'mild';
      }
      else if ($difference <= 7) {
        $category = 'moderate';
      }
      else {
        $category = 'strong';
      }
      $highscoreindex = $dimensionscores[0] > $dimensionscores[1] ? 0 : 1;

      $style = $styles[$highscoreindex];

      $heading = get_string('userrecommendationsheading', 'alstea', [
        'category'  => get_string("stylecategorymidsentence:{$category}", 'alstea'),
        'dimension' => get_string("dimension:{$dimension}", 'alstea'),
        'style'     => get_string("stylemidsentence:{$style}", 'alstea'),
      ]);
      return $heading;
    }, array_keys($learningstyles));
    
    return $data;
  }
}
?>