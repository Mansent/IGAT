<?php
defined('MOODLE_INTERNAL') || die();

require_once('classes/lib/igat_usersettings.php');
require_once('classes/forms/usersettings_form.php');
require_once('classes/lib/igat_capabilities.php');

/**
 * Responsible for managing and rendering the user settings tab in the gamification view 
 */
class usersettings_renderer 
{
  private $courseId; 
  
  private $lib_usersettings;
  private $lib_capabilities;
  
  /* 
   * Creates a new usersettings renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
		$this->lib_usersettings = new igat_usersettings($courseId);
    $this->lib_capabilities = new igat_capabilities();
	}  
  
  /**
   * Renders the levels tab
   */
  public function render_tab() { 
    global $USER;
    if($this->lib_capabilities->isManagerOrTeacher($this->courseId, $USER->id)) {
      echo '<p>Students can choose to be anonymous on the leaderboard and decide if the full leaderboard or a leaderboard limited to 5 students above and below the current student rank is displayed. The leaderboard can also be hidden.</p>';
    }
    else 
    {
      $currentSettings = $this->lib_usersettings->getUsersettings($USER->id);
    
      //Instantiate usersettings form
      $mform = new usersettings_form("/blocks/igat/dashboard.php?courseid=" . $this->courseId . "&tab=settings");
         
      if ($form_res = $mform->get_data()) {		
        // save settings
        $this->lib_usersettings->saveUsersettings($USER->id, $form_res);
      
        // save message
        echo '<span class="notifications" id="user-notifications"><div class="alert alert-info alert-block fade in " role="alert" data-aria-autofocus="true" tabindex="0">
            <button type="button" class="close" data-dismiss="alert">×</button>
            The settings have been saved!
        </div></span>';
        
        // show form
        $mform->set_data($form_res);
        $mform->display();
      } 
      else {
        $mform->set_data($currentSettings);
        $mform->display();
      }
    }
	}
}