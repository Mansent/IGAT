<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Helper class for quickly checking the users rights.
 */
class igat_capabilities
{
	/**
	 * Checks if the user is student and has no teacher or manager roles.
	 * @param int $courseId the id of the course to check for
	 * @param int $userId the id of the user to check for
	 * @return boolean true if the user is only student
	 */
	public function isStudent($courseId, $userId) {
		$context = get_context_instance(CONTEXT_COURSE, $courseId);
		$roles = get_user_roles($context, $userId, false);
		foreach($roles as &$role) {
			if($role->shortname == "manager" || $role->shortname == "editingteacher" ||$role->shortname == "teacher") {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Checks if the user is admin, manager, teacher or non-editing teacher.
	 * @param int $courseId the id of the course to check for
	 * @param int $userId the id of the user to check for
	 * @return boolean true if the user is manager or teacher
	 */
	public function isManagerOrTeacher($courseId, $userId) {
		$context = get_context_instance(CONTEXT_COURSE, $courseId);
		$roles = get_user_roles($context, $userId, false);
		foreach($roles as &$role) {
			if($role->shortname == "manager" || $role->shortname == "editingteacher" ||$role->shortname == "teacher") {
				return true;
			}
		}
    
    //check for admin
    $admins = get_admins();
    $isadmin = false;
    foreach($admins as $admin) {
        if ($userId == $admin->id) {
            return true;
        }
    }
		return false;
	}
}