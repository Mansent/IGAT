<?php
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
		echo '<p>gamification_dashboard_analytics_renderer</p>';
		?>
		<!-- https://seiyria.com/bootstrap-slider/  -->
		<input id="ex12c" type="text"/><br/>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js" crossorigin="anonymous"></script>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"  crossorigin="anonymous">
		<script>
				var sliderC = new Slider("#ex12c", { id: "slider12c", min: 0, max: 10, range: true, tooltip: 'always', value: [3, 7] });
		</script>
		<style>
			#slider12c .slider-selection {
				background: red;
			}
		</style>
<?php
	}
}
 ?>