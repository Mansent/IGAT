/**
 * This script handles the dynamic elements on the gamification analytics page
 */

define(['jquery'], function($) { 
    return {
      /**
       * Generates a slider form the element with the given id 
       */
      initSlider: function(id, chartId, courseId) {          
        var slider = new Slider("#" + id, { id: "slider" + id, min: -11, max: 11, range: true, value: [-11, 11] });
        slider.on("slide", function(sliderValue) {
          document.getElementById(id + "min").textContent = sliderValue[0];
          document.getElementById(id + "max").textContent = sliderValue[1];
          updateGraphData(chartId, courseId);
        });
      }, 
      initDatePicker: function(chartId, courseId) {          
        var datePickerMin = document.getElementById("minDate" + chartId);
        var datePickerMax = document.getElementById("maxDate" + chartId);
        datePickerMin.value = "";
        datePickerMax.value = "";
        datePickerMin.addEventListener("change", function(obj) {
          updateGraphData(chartId, courseId);
        });
        datePickerMax.addEventListener("change", function(obj) {
          updateGraphData(chartId, courseId);
        });
      }
    };
});


var lastProcessingMin, lastProcessingMax, lastPerceptionMin, lastPerceptionMax, lastInputMin, lastInputMax;
var lastUnderstandingMin, lastUnderstandingMax;
var lastDateMin, lastDateMax;

/*
 * Gets recent filtered data for a chart from the server and redraws the chart
 */
function updateGraphData(id, courseId) {
  var processingMin = document.getElementById('processing' + id + 'min').innerText;
  var perceptionMin = document.getElementById('perception' + id + 'min').innerText;
  var inputMin = document.getElementById('input' + id + 'min').innerText;
  var understandingMin = document.getElementById('understanding' + id + 'min').innerText;
  var processingMax = document.getElementById('processing' + id + 'max').innerText;
  var perceptionMax = document.getElementById('perception' + id + 'max').innerText;
  var inputMax = document.getElementById('input' + id + 'max').innerText;
  var understandingMax = document.getElementById('understanding' + id + 'max').innerText;
  
  var datePickerMin = document.getElementById("minDate" + id);
  var datePickerMax = document.getElementById("maxDate" + id);
  var minDate = "";
  var maxDate = "";
  if(datePickerMin != undefined && datePickerMax != undefined) {
    minDate = datePickerMin.value;
    maxDate = datePickerMax.value;
  }
  
  if(processingMin != lastProcessingMin || processingMax != lastProcessingMax // Only make a request if some slider value changed
    || perceptionMin != lastPerceptionMin || perceptionMax != lastPerceptionMax
    || inputMin != lastInputMin || inputMax != lastInputMax
    || understandingMin != lastUnderstandingMin || understandingMax != lastUnderstandingMax
    || minDate != lastDateMin || maxDate != lastDateMax) 
    {
      //Do server request
      var filterData = {
        'graphid': id,
        'courseid': courseId,
        'processingMin': processingMin,
        'processingMax': processingMax,
        'perceptionMin': perceptionMin,
        'perceptionMax': perceptionMax,
        'inputMin': inputMin,
        'inputMax': inputMax,
        'understandingMin': understandingMin,
        'understandingMax': understandingMax,
        'minDate': minDate,
        'maxDate': maxDate
      };
      console.log(filterData);
      
      $.ajax({
          type: "POST",
          async: "false",
          url: "/blocks/igat/ajax.php",
          data: filterData,
          success: function(json) {
            if(id == 3) { // dashboard subsequent pages
              var newData = JSON.parse(json);
              for(var htmlId in newData) {
                document.getElementById(htmlId).innerText = newData[htmlId];
              }
            }
            else if(id == 6) { // gamification feedback rate
              var feedbackRate = json;
              document.getElementById("feedbackRate").innerText = feedbackRate;
            }
            else { // line chart or bar chart
              var newDatasets = JSON.parse(json);
              if(typeof newDatasets["labels"] !== 'undefined') { // We got a response with labels and data
                config[id].data.labels = newDatasets["labels"];
                config[id].data.datasets = newDatasets["data"];
              }
              else { // Response only contains data
                config[id].data.datasets = newDatasets;
              }
              chart[id].update();
            }
          },
          error: function(result) {
            console.log("Error updating learning styles");
            console.log(result);
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
      lastDateMin = minDate;
      lastDateMax = maxDate;
    }
}
