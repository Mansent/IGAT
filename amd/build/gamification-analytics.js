/**
 * This script handles the dynamic elements on the gamification analytics page
 */

var lastProcessingMin, lastProcessingMax, lastPerceptionMin, lastPerceptionMax, lastInputMin, lastInputMax, lastUnderstandingMin, lastUnderstandingMax;

/*
 * Gets recent filtered data for a chart from the server and redraws the chart
 */
function updateGraphData(id, courseId) {
  let processingMin = document.getElementById('processing' + id + 'min').innerText;
  let perceptionMin = document.getElementById('perception' + id + 'min').innerText;
  let inputMin = document.getElementById('input' + id + 'min').innerText;
  let understandingMin = document.getElementById('understanding' + id + 'min').innerText;
  let processingMax = document.getElementById('processing' + id + 'max').innerText;
  let perceptionMax = document.getElementById('perception' + id + 'max').innerText;
  let inputMax = document.getElementById('input' + id + 'max').innerText;
  let understandingMax = document.getElementById('understanding' + id + 'max').innerText;
  
  if(processingMin != lastProcessingMin || processingMax != lastProcessingMax // Only make a request if some slider value changed
    || perceptionMin != lastPerceptionMin || perceptionMax != lastPerceptionMax
    || inputMin != lastInputMin || inputMax != lastInputMax
    || understandingMin != lastUnderstandingMin || understandingMax != lastUnderstandingMax) 
    {
      //Do server request
      let filterData = {
        'courseid': courseId,
        'processingMin': processingMin,
        'processingMax': processingMax,
        'perceptionMin': perceptionMin,
        'perceptionMax': perceptionMax,
        'inputMin': inputMin,
        'inputMax': inputMax,
        'understandingMin': understandingMin,
        'understandingMax': understandingMax
      };
      
      $.ajax({
          type: "POST",
          async: "false",
          url: "/blocks/igat/ajax.php",
          data: filterData,
          success: function(html) {
              console.log(html);
          },
          error: function(html) {
              console.log(html);
          }
      });
      
      // update change buffer variables
      lastProcessingMin = processingMin;
      lastProcessingMax = processingMax;
      lastPerceptionMin = perceptionMin;
      lastPerceptionMax = perceptionMax;
      lastInputMin = inputMin;
      lastInputMax = inputMax;
      lastUnderstandingMin = understandingMin;
      lastUnderstandingMax = understandingMax;
    }
}

define(['jquery'], function($) { 
    return {
      /**
       * Generates a slider form the element with the given id 
       */
      initSlider: function(id, chartId, courseId) {					
        let slider = new Slider("#" + id, { id: "slider" + id, min: -11, max: 11, range: true, value: [-11, 11] });
        slider.on("slide", function(sliderValue) {
          document.getElementById(id + "min").textContent = sliderValue[0];
          document.getElementById(id + "max").textContent = sliderValue[1];
          updateGraphData(chartId, courseId);
        });
      }
    };
});