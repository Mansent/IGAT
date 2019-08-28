<?php
/**
 * Renders individual components for the gamification analytics page 
 */
class analytics_components_renderer 
{ 
	private $sliderInit = false;
	
  /**
   * Renders a learning style dimension slider
	 * $id string the id of the html slider element
	 * $minDimension string the description of the slider  minimum dimension
	 * $maxDimension string the description of the slider  maximum dimension
   */
  public function renderSlider($id, $minDimension, $maxDimension) {  
		global $PAGE;
		if(!$this->sliderInit) {?>
			<!-- Slider base on https://seiyria.com/bootstrap-slider/  --> 		
			<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/bootstrap-slider.min.js" crossorigin="anonymous"></script>
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-slider/10.6.2/css/bootstrap-slider.min.css"  crossorigin="anonymous">
			<style> 
				.slider-selection {
					background: red;
				}
			</style>
<?php $this->sliderInit = true;
		} ?>
		<div class="lsSliderContainer">
			<div class="lsSliderDimensionLeft"><?php echo $minDimension; ?></div>
			<div class="lsSlider">
				<div id="<?php echo $id; ?>min" class="sliderMin">-11</div>
				<div id="<?php echo $id; ?>max" class="sliderMax">11</div>
				<input id="<?php echo $id; ?>" type="text" />
			</div>
			<div class="lsSliderDimensionRight"><?php echo $maxDimension; ?></div>
		</div>
<?php
		$PAGE->requires->js_call_amd('block_igat/gamification-analytics', 'initSlider', array($id)); 
	}
}
 ?>