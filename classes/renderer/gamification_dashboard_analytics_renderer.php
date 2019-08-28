<?php
require_once('classes/renderer/analytics_components_renderer.php');
/**
 * Responsible for gererating and rendering the gamification dashboard analytics 
 */
class gamification_dashboard_analytics_renderer 
{
  private $courseId; 
  
  /* 
   * Creates a new gamification dashboard analytics renderer renderer 
   * @param courseId the id of the current course.
   */
	public function __construct($courseId) {
		$this->courseId = $courseId;
	}  
	
  /**
   * Renders the gamification dashboard analytics tab
   */
  public function render_tab() { 
		$ac_renderer = new analytics_components_renderer();
		$ac_renderer->renderSlider('processing', 'active', 'reflective');
		$ac_renderer->renderSlider('perception', 'sensing', 'intuitive');
		$ac_renderer->renderSlider('input', 'visual', 'verbal');
		$ac_renderer->renderSlider('understanding', 'sequential', 'global');
	}
}
 ?>