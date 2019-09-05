<?php
/**
 * Block igat is defined here.
 *
 * @package     block_igat
 * @copyright   2019 Manuel Gottschlich <manuel.gottschlich@rwth-aachen.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
 
require_once('classes/lib/igat_progress.php');
require_once('classes/lib/igat_badges.php');
require_once('classes/lib/igat_ranks.php');
require_once('classes/lib/igat_capabilities.php');

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
        global $COURSE, $USER;
        
        $lib_progress = new igat_progress($COURSE->id);
        $lib_badges = new igat_badges($COURSE->id);
        $lib_ranks = new igat_ranks($COURSE->id);
				$lib_capabilities = new igat_capabilities();

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
        
				
        
			if($lib_capabilities->isManagerOrTeacher($COURSE->id, $USER->id)) {
				$dashboardUrl = new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $COURSE->id, 'tab' => 'progress'));
        $analyticsUrl = new moodle_url('/blocks/igat/analytics.php', array('courseid' => $COURSE->id));
				
				$this->content->text = '
					<a href="' . $dashboardUrl . '">
            <div class="igatcard igatyellow">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/achievement.png"/> Students	
              </div>
              <div class="igatlistinfo">Gamification Dashboard</div>
            </div>
          </a>
					<a href="' . $analyticsUrl . '">
            <div class="igatcard igatyellow">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/analytics.png"/> Analytics	
              </div>
              <div class="igatlistinfo">Gamification Analytics</div>
            </div>
          </a>';
			}
			else {
        $progressUrl = new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $COURSE->id, 'tab' => 'progress'));
        $badgesUrl = new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $COURSE->id, 'tab' => 'badges'));
        $ranksUrl = new moodle_url('/blocks/igat/dashboard.php', array('courseid' => $COURSE->id, 'tab' => 'ranks'));
        
        $this->content->text = ' 
          <a href="' . $progressUrl . '">
            <div class="igatcard igatgreen">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/graduation.png"/> Progress
              </div>
              <div class="igatlistinfo"><b>' . $lib_progress->getPointsToNextLevel($USER->id) . ' points</b> left until the next level!</div>
            </div>
          </a>
          <a href="' . $badgesUrl . '">
            <div class="igatcard igatblue">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/achievement.png"/> Badges
              </div>
              <div class="igatlistinfo">' . $lib_badges->getRandomOpenBadgeCriterion($USER->id) . '</div>
            </div>
          </a>
          <a href="' . $ranksUrl . '">
            <div class="igatcard igatyellow">
              <div class="igatleftblock">
                <img class="igateyecatcher" width="50" height="50" src="/blocks/igat/img/podium.png"/> Ranks
              </div>
              <div class="igatlistinfo">' . $lib_ranks->getRanksStatusMessage($USER->id) . '</div>
            </div>
          </a>';
			}
			
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
