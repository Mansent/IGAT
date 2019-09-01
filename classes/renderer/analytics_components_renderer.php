<?php
/**
 * Renders individual components for the gamification analytics page 
 */
class analytics_components_renderer 
{ 
	private $courseId;
  private $sliderInit = false;
  
  /**
   * Creates a new instance of this library
   */
  public function __construct($courseId) {
    $this->courseId = $courseId;
  }
	
  /**
   * Renders a learning style dimension slider
	 * $id string the id of the html slider element
	 * $id int the id of the chart
	 * $minDimension string the description of the slider  minimum dimension
	 * $maxDimension string the description of the slider  maximum dimension
   */
  public function renderSlider($id, $chartId, $minDimension, $maxDimension) {  
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
		$PAGE->requires->js_call_amd('block_igat/gamification-analytics', 'initSlider', array($id, $chartId, $this->courseId)); 
	}
	
	/**
	 * Renders a filter for all learing style dimensions
	 * @param int unique id the id of the filter
	 */
	public function renderLsFilter($id) { ?>
		<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseFilter<?php echo $id; ?>" aria-expanded="false" aria-controls="collapseExample">
			Filter Learning Style
		</button>
		<div class="collapse" id="collapseFilter<?php echo $id; ?>">
			<div class="card card-body filtercard">
<?php
				$this->renderSlider('processing' . $id, $id, 'active', 'reflective');
				$this->renderSlider('perception' . $id, $id, 'sensing', 'intuitive');
				$this->renderSlider('input' . $id, $id, 'visual', 'verbal');
				$this->renderSlider('understanding' . $id, $id, 'sequential', 'global'); ?>
			</div>
	</div>
<?php
	}
	
	/**
	 * Renders a line chart containing data for each gamification dashboard tab
	 * @param int $id an unique id for this chart
	 * @param array $labels the labels for the x axis
	 * @param string $yAxisName the text description of the y axis
	 * @param array $progressData the data values for the progess tab 
	 * @param array $badgesData the data values for the badges tab 
	 * @param array $ranksData the data values for the ranks tab 
	 * @param array $settingsData the data values for the settings tab 
	 */
	public function renderDashboardLineChart($id, $labels, $yAxisName, $progressData, $badgesData, $ranksData, $settingsData) { ?>
		<div class="analyticsChart"><canvas id="dashboardChart<?php echo $id; ?>"></canvas></div>
    <script>
    var ctx = document.getElementById("dashboardChart<?php echo $id; ?>").getContext('2d');

    var labels = [<?php echo '"' . implode('", "', $labels) . '"'; ?>];

    if(typeof chart == "undefined" && typeof config == "undefined") {
      var chart = [];
      var config = [];
    }
    
    config[<?php echo $id; ?>] = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                  label: 'Progress tab',
                  data: [<?php echo implode(", ", $progressData); ?>], 
                  fill: false,
                  backgroundColor: [
                      'rgba(0, 123, 255, 0.4)'
                  ],
                  borderColor: [
                      'rgba(0, 123, 255, 1)'
                  ]
              },
              {
                  label: 'Badges tab', 
                  data: [<?php echo implode(", ", $badgesData); ?>],
                  fill: false,
                  backgroundColor: [
                      'rgba(255, 193, 7, 0.4)'
                  ],
                  borderColor: [
                      'rgba(255, 193, 7, 1)'
                  ]
              },
              {
                  label: 'Ranks tab',
                  data: [<?php echo implode(", ", $ranksData); ?>], 
                  fill: false,
                  backgroundColor: [
                      'rgba(220, 53, 69, 0.4)'
                  ],
                  borderColor: [
                      'rgba(220, 53, 69, 1)'
                  ]
              },
              {
                  label: 'Settings tab', 
                  data: [<?php echo implode(", ", $settingsData); ?>], 
                  fill: false,
                  backgroundColor: [ 
                      'rgba(40, 167, 69, 0.4)'
                  ],
                  borderColor: [
                      'rgba(40, 167, 69, 1)'
                  ]
            }]
        },
        options: {
          responsive: true, // Instruct chart js to respond nicely.
          maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
					scales: {
						xAxes: [{
							display: true,
							scaleLabel: {
								display: true,
								labelString: 'Date'
							}
						}],
						yAxes: [{
							ticks: {
								beginAtZero: true
							},
							display: true,
							scaleLabel: {
								display: true,
								labelString: '<?php echo $yAxisName; ?>'
							}
						}]
					}
        }
    };
    chart[<?php echo $id; ?>] = new Chart(ctx, config[<?php echo $id; ?>]);
    </script>
<?php		
	}
  
  /**
   * Outputs the datasets for a line chart in json format for ajax
	 * @param array $progressData the data values for the progess tab 
	 * @param array $badgesData the data values for the badges tab 
	 * @param array $ranksData the data values for the ranks tab 
	 * @param array $settingsData the data values for the settings tab 
   */
  public function printJsonDashboardLineChartDatasets($progressData, $badgesData, $ranksData, $settingsData) { ?>
    [{
        "label": "Progress tab",
        "data": [<?php echo implode(", ", $progressData); ?>], 
        "fill": false,
        "backgroundColor": [
            "rgba(0, 123, 255, 0.4)"
        ],
        "borderColor": [
            "rgba(0, 123, 255, 1)"
        ]
    },
    {
        "label": "Badges tab", 
        "data": [<?php echo implode(", ", $badgesData); ?>],
        "fill": false,
        "backgroundColor": [
            "rgba(255, 193, 7, 0.4)"
        ],
        "borderColor": [
            "rgba(255, 193, 7, 1)"
        ]
    },
    {
        "label": "Ranks tab",
        "data": [<?php echo implode(", ", $ranksData); ?>], 
        "fill": false,
        "backgroundColor": [
            "rgba(220, 53, 69, 0.4)"
        ],
        "borderColor": [
            "rgba(220, 53, 69, 1)"
        ]
    },
    {
        "label": "Settings tab", 
        "data": [<?php echo implode(", ", $settingsData); ?>], 
        "fill": false,
        "backgroundColor": [ 
            "rgba(40, 167, 69, 0.4)"
        ],
        "borderColor": [
            "rgba(40, 167, 69, 1)"
        ]
    }]
<?php
  }
	
	/**
	 * Renders a line chart containing data for each gamification dashboard tab
	 * @param int $id an unique id for this chart
	 * @param array $labels the labels for the x axis
	 * @param array $data the data values for the chart 
	 * @param string $yAxisName the text description of the y axis
	 * @param array $datasetName the name of the dataset
	 */
	public function renderDashboardBarChart($id, $labels, $data, $yAxisName, $datasetName) { ?>
		<div class="analyticsChart"><canvas id="dashboardChart<?php echo $id; ?>"></canvas></div>
    <script>
    var ctx = document.getElementById("dashboardChart<?php echo $id; ?>").getContext('2d');

    // End Defining data
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [<?php echo '"' . implode('", "', $labels) . '"'; ?>],
            datasets: [{
                label: '<?php echo $datasetName; ?>', // Name the series
                data: [<?php echo implode(', ', $data); ?>], // Specify the data values array
								fill: false,
								backgroundColor: [ // Specify custom colors
                    'rgba(0, 123, 255, 0.4)',
                    'rgba(255, 193, 7, 0.4)',
                    'rgba(220, 53, 69, 0.4)',
                    'rgba(40, 167, 69, 0.4)'
										
                ],
                borderColor: [ // Add custom color borders
                    'rgba(0, 123, 255, 1)',
                    'rgba(255, 193, 7, 1)',
                    'rgba(220, 53, 69, 1)',
                    'rgba(40, 167, 69, 1)'
                ]
            }]
        },
        options: {
          responsive: true, // Instruct chart js to respond nicely.
          maintainAspectRatio: false, // Add to prevent default behaviour of full-width/height 
					scales: {
						xAxes: [{
							display: true,
							scaleLabel: {
								display: false
							}
						}],
						yAxes: [{
							ticks: {
								beginAtZero: true
							},
							display: true,
							scaleLabel: {
								display: true,
								labelString: '<?php echo $yAxisName; ?>'
							}
						}]
					}
        }
    });
    </script>
<?php		
	}
}
 ?>