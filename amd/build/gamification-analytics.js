/**
 * This script handles the dynamic elements on the gamification analytics page
 */

define(['jquery'], function($) { 
    return {
				/**
				 * Generates a slider form the element with the given id 
				 */
        initSlider: function(id) {					
					let slider = new Slider("#" + id, { id: "slider" + id, min: -11, max: 11, range: true, value: [-11, 11] });
					slider.on("slide", function(sliderValue) {
						document.getElementById(id + "min").textContent = sliderValue[0];
						document.getElementById(id + "max").textContent = sliderValue[1];
					});
        }
    };
});