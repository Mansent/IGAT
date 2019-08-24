<?php
require_once("$CFG->libdir/formslib.php");
 
/**
 * From for changing the user settings in the gamification dashboard settings tab
 */
class usersettings_form extends moodleform {
	
    public function definition() { 
        $mform = $this->_form;				
				
				// Anonymity in leaderboard
				$mform->addElement('advcheckbox', 'anonymousleaderboard', 
					'Anonymity in leaderboard', 'Hide my name in the ranks tab for other students');
					
				//leaderboard display type
				$leaderboard_display_types = array('all' => 'All students', 
																					 'limited' => '5 studens ahead of me and behind me', 
																					 'hide' => 'Hide ranks');
				$mform->addElement('select', 'leaderboarddisplay', 'Ranks display', $leaderboard_display_types);
				
				$this->add_action_buttons(false);
    }
}
?>