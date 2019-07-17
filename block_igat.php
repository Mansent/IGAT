<?php
/**
 * Block igat is defined here.
 *
 * @package     block_igat
 * @copyright   2019 Manuel Gottschlich <manuel.gottschlich@rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * igat block.
 *
 * @package    block_igat
 * @copyright  2019 Manuel Gottschlich <manuel.gottschlich@rwth-aachen.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_igat extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_igat');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {

        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array('');
        $this->content->icons = array('');

        $badgesUrl = new moodle_url('/blocks/igat/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'tab' => 'badges'));
        $levelUrl = new moodle_url('/blocks/igat/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'tab' => 'level'));
        $ranksUrl = new moodle_url('/blocks/igat/view.php', array('blockid' => $this->instance->id, 'courseid' => $COURSE->id, 'tab' => 'ranks'));
        
        $this->content->text = ' 
          <a href="' . $badgesUrl . '">
            <div class="igatcard igatblue">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/achievement.png"/> Badges
              </div>
              <div class="igatlistinfo">Submit assignment 2 to <b>earn a badge!</b></div>
            </div>
          </a>
          <a href="' . $levelUrl . '">
            <div class="igatcard igatgreen">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/graduation.png"/> Level
              </div>
              <div class="igatlistinfo"><b>20 points</b> left until the next level!</div>
            </div>
          </a>
          <a href="' . $ranksUrl . '">
            <div class="igatcard igatyellow">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/podium.png"/> Ranks
              </div>
              <div class="igatlistinfo">5 points left to catch up <b>Peter Lauber</b></div>
            </div>
          </a>
        ';
        
        
      $this->content->footer = '';

      return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediatly after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        $this->title = get_string('blocktitle', 'block_igat');
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'course-view' => true,
        );
    }
}
