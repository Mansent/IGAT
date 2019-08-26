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
      return false;
    }
    $datasetId = $this->getUserDatasetId($userId);
    if($datasetId == null) {
      return false;
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
  
  public function recom($dataset) {
    $scores = $this->datasetapi->get_scores_for_dataset($dataset);

        $learningstyles = $this->learningstylesapi->get_learning_styles();

        $data = array_map(function (string $dimension) use ($learningstyles, $scores): array {
            $styles = $learningstyles[$dimension];

            $dimensionscores = array_map(function (string $style) use ($scores): int {
                return (int) $scores[$style];
            }, $styles);

            $difference = abs($dimensionscores[0] - $dimensionscores[1]);

            $category = $this->get_category_for_score_difference($difference);

            $highscoreindex = $dimensionscores[0] > $dimensionscores[1] ? 0 : 1;

            $style = $styles[$highscoreindex];

            $heading = get_string('userrecommendationsheading', 'alstea', [
                'category'  => get_string("stylecategorymidsentence:{$category}", 'alstea'),
                'dimension' => get_string("dimension:{$dimension}", 'alstea'),
                'style'     => get_string("stylemidsentence:{$style}", 'alstea'),
            ]);

            $content = get_string("userrecommendations:{$category}_{$style}", 'alstea');

            return compact('heading', 'content');
        }, array_keys($learningstyles));
        
        echo '<pre>' . var_export($data, true) . '</pre>';
  }
  
      /**
     * Return the according category name for the given score difference.
     *
     * @param int $difference Score difference.
     *
     * @return string Category name.
     */
    private function get_category_for_score_difference(int $difference): string {
        if ($difference <= 3) {
            return 'mild';
        }

        if ($difference <= 7) {
            return 'moderate';
        }

        return 'strong';
    }
}

?>