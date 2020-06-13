<?php
defined('MOODLE_INTERNAL') || die();

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
			Filter
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
	 * Renders a filter for all learing style dimensions and for a date
	 * @param int id the unique id of the chart
	 */
  public function renderLsDateFilter($id) {
		global $PAGE; ?>
		<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseFilter<?php echo $id; ?>" aria-expanded="false" aria-controls="collapseExample">
			Filter
		</button>
		<div class="collapse" id="collapseFilter<?php echo $id; ?>">
			<div class="card card-body filtercard">
<?php
				$this->renderSlider('processing' . $id, $id, 'active', 'reflective');
				$this->renderSlider('perception' . $id, $id, 'sensing', 'intuitive');
				$this->renderSlider('input' . $id, $id, 'visual', 'verbal');
				$this->renderSlider('understanding' . $id, $id, 'sequential', 'global'); ?>
        
        <div class="dateFilter">
        <label>Filter from</label>
        <input id="minDate<?php echo $id; ?>" type="date" value="" />
        <label>Filter to</label>
        <input id="maxDate<?php echo $id; ?>" type="date" value="" />
        </div>
			</div>
	</div>
    <?php
    $PAGE->requires->js_call_amd('block_igat/gamification-analytics', 'initDatePicker', array($id, $this->courseId));
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
                  label: 'Leaderboard tab',
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
  public function printJsonDashboardLineChartDatasets($progressData, $badgesData, $ranksData, $settingsData, $labels) { ?>
    { 
      "data":
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
        }],
      "labels": ["<?php echo implode('", "', $labels); ?>"]
    }
<?php
  }
	
	/**
	 * Renders a line chart containing data for each gamification dashboard tab
	 * @param int $id an unique id for this chart
	 * @param array $labels the labels for the x axis
	 * @param array $data the data values for the chart 
	 * @param string $yAxisName the text description of the y axis
	 * @param array $datasetName the name of the dataset
	 * @param boolean $enableColors enable bar colering
	 */
	public function renderBarChart($id, $labels, $data, $yAxisName, $datasetName, $enableColors) { ?>
		<div class="analyticsChart"><canvas id="dashboardChart<?php echo $id; ?>"></canvas></div>
    <script>
    var ctx = document.getElementById("dashboardChart<?php echo $id; ?>").getContext('2d');
    
    if(typeof chart == "undefined" && typeof config == "undefined") {
      var chart = [];
      var config = [];
    }
    
    config[<?php echo $id; ?>] = {
        type: 'bar',
        data: {
            labels: [<?php echo '"' . implode('", "', $labels) . '"'; ?>],
            datasets: [{
                label: '<?php echo $datasetName; ?>', // Name the series
                data: [<?php echo implode(', ', $data); ?>], // Specify the data values array
								fill: false,
<?php 				if($enableColors) { ?>
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
<?php 				}
							else { ?>
								backgroundColor: 'rgba(0, 123, 255, 0.4)',
                borderColor: 'rgba(0, 123, 255, 1)'
<?php 				} ?>
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
    };
    
    chart[<?php echo $id; ?>] = new Chart(ctx, config[<?php echo $id; ?>]);
    </script>
<?php		
	}
	/**
	 * Outputs the data for a bar chart json format
	 * @param array $datasetName the name of the dataset
	 * @param array $data the data values for the chart 
   */   
  public function printJsonBarChartDataset($datasetName, $data, $enableColors) { ?>
    [{
      "label": "<?php echo $datasetName; ?>", 
      "data": [<?php echo implode(', ', $data); ?>],
      "fill": false,
<?php 				if($enableColors) { ?>
								"backgroundColor": [ 
                    "rgba(0, 123, 255, 0.4)",
                    "rgba(255, 193, 7, 0.4)",
                    "rgba(220, 53, 69, 0.4)",
                    "rgba(40, 167, 69, 0.4)"
										
                ],
                "borderColor": [
                    "rgba(0, 123, 255, 1)",
                    "rgba(255, 193, 7, 1)",
                    "rgba(220, 53, 69, 1)",
                    "rgba(40, 167, 69, 1)"
                ]
<?php 				}
							else { ?>
								"backgroundColor": "rgba(0, 123, 255, 0.4)",
                "borderColor": "rgba(0, 123, 255, 1)"
<?php 				} ?>
    }] 
<?php    
  }
	
	/**
	 * Renders the subsequent pages graph
	 * @param data array the data for the graph 
	 */
	public function renderSubsequentPagesGraph($data) { ?>
		<p class="graphContainer">
			<span id="progressToBadges" class="edgeWeight"><?php echo $data['progress']['badges']; ?>%</span>
			<span id="progressToRanks" class="edgeWeight"><?php echo $data['progress']['ranks']; ?>%</span>
			<span id="progressToSettings" class="edgeWeight"><?php echo $data['progress']['settings']; ?>%</span>
			<span id="badgesToProgress" class="edgeWeight"><?php echo $data['badges']['progress']; ?>%</span>
			<span id="badgesToRanks" class="edgeWeight"><?php echo $data['badges']['ranks']; ?>%</span>
			<span id="badgesToSettings" class="edgeWeight"><?php echo $data['badges']['settings']; ?>%</span>
			<span id="ranksToProgress" class="edgeWeight"><?php echo $data['ranks']['progress']; ?>%</span>
			<span id="ranksToBadges" class="edgeWeight"><?php echo $data['ranks']['badges']; ?>%</span>
			<span id="ranksToSettings" class="edgeWeight"><?php echo $data['ranks']['settings']; ?>%</span>
			<span id="settingsToProgress" class="edgeWeight"><?php echo $data['settings']['progress']; ?>%</span>
			<span id="settingsToBadges" class="edgeWeight"><?php echo $data['settings']['badges']; ?>%</span>
			<span id="settingsToRanks" class="edgeWeight"><?php echo $data['settings']['ranks']; ?>%</span>
			<span id="progressToMoodle" class="edgeWeight"><?php echo $data['progress']['moodle']; ?>%</span>
			<span id="badgesToMoodle" class="edgeWeight"><?php echo $data['badges']['moodle']; ?>%</span>
			<span id="ranksToMoodle" class="edgeWeight"><?php echo $data['ranks']['moodle']; ?>%</span>
			<span id="settingsToMoodle" class="edgeWeight"><?php echo $data['settings']['moodle']; ?>%</span>
			<span id="progressToExternal" class="edgeWeight"><?php echo $data['progress']['external']; ?>%</span>
			<span id="badgesToExternal" class="edgeWeight"><?php echo $data['badges']['external']; ?>%</span>
			<span id="ranksToExternal" class="edgeWeight"><?php echo $data['ranks']['external']; ?>%</span>
			<span id="settingsToExternal" class="edgeWeight"><?php echo $data['settings']['external']; ?>%</span>
			<img src="/blocks/igat/img/graph.png" width="800" />
		</p>
<?php	
	}
	
	/**
	 * Renders the subsequent pages json for the ajax call.
	 * @param $data the subsequent pages statistics datta
	 */
	public function renderSubsequentPagesJSON($data) {
		$res = array();
		$tabs = array('progress', 'badges', 'ranks', 'settings', 'moodle', 'external');
		foreach($tabs as &$from) {
			foreach($tabs as &$to) {
				if($from != 'external' && $from != 'moodle' && $from != $to) {
					$res[$from . 'To' . ucfirst($to)] = $data[$from][$to] . '%';
				}
			}
		}
		echo json_encode($res);
	}
}
 ?>