<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
 
/**
 * From for changing the user settings in the gamification dashboard settings tab
 */
class usersettings_form extends moodleform {
	
    public function definition() { 
        $mform = $this->_form;				
				
				// Anonymity in leaderboard
				$mform->addElement('advcheckbox', 'anonymousleaderboard', 
					'Anonymity in leaderboard', 'Show <b>anonymous</b> instead of my name in the leaderboard tab for all students and hide all other students names for me.');
					
				//leaderboard display type
				$leaderboard_display_types = array('all' => 'All students', 
																					 'limited' => '5 students ahead of me and behind me', 
																					 'hide' => 'Hide leaderboard');
				$mform->addElement('select', 'leaderboarddisplay', 'Leaderboard display', $leaderboard_display_types);
				
				$this->add_action_buttons(false);
    }
}
?>