<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_pthranking
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
$document = JFactory::getDocument();
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthprofile.js?tx=20161217_1838'); // chart.js is already included by template
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css?tx=20161220_1858');
?>
<input type="hidden" name="userid" id="userid" value="<?php echo $this->userid?>" />
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div>
            <?php if($this->userexists): ?>
            <h1>Profile: <?php echo $this->usernameExt?></h1>
            <h3>Ranking information about this season (beta phase 2016-12):</h3>
            <?php echo $this->basicinfo_html; ?>
            <hr />
            <h3>Statistics for this season (beta phase 2016-12):</h3>
            <?php echo $this->seasonpiedata; ?>
            <canvas id="seasonPie" width="100" height="100" class="canvas-holder half"></canvas>
            <canvas id="seasonChart" width="150" height="100" class="canvas-holder half"></canvas>
            <div style="clear: left;"></div>
            <hr />
            <h3>Statistics for all time:</h3>
            <?php echo $this->alltimepiedata; ?>
            <canvas id="alltimePie" width="100" height="100" class="canvas-holder half"></canvas>
            <canvas id="alltimeChart" width="150" height="100" class="canvas-holder half"></canvas>
            <div style="clear: left;"></div>

            <hr />
            <h3>Last 20 Games played:</h3>
            <div id="lastGames"></div>
            <?php else: ?>
            <p>Player not found</p>
            
            <?php endif; ?>
            <!-- Modal -->
            <div id="tableModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
              <div class="modal-header" id="tableModalHeader">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                <h3 id="tableModalLabel"></h3>
              </div>
              <div class="modal-body" id="tableModalBody">

              </div>
              <div class="modal-footer" id="tableModalFooter">
                <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
              </div>
            </div>
        </div>
    </div>
</div>
<script>
  document.onreadystatechange = function() {
      if (document.readyState === 'complete') {
         var sChart = jQuery("#seasonChart");
          var msChart = new Chart(sChart, {
              type: 'bar',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      label: '# of Places',
                      data: [<?php echo implode(",", $this->season_data) ?>],
                      backgroundColor: [
                          'rgba(86, 226, 137, 1.0)',
                          'rgba(104, 226, 86, 1.0)',
                          'rgba(174, 226, 86, 1.0)',
                          'rgba(226, 297, 86, 1.0)',
                          'rgba(226, 137, 86, 1.0)',
                          'rgba(226, 84, 104, 1.0)',
                          'rgba(226, 86, 174, 1.0)',
                          'rgba(207, 86, 226, 1.0)',
                          'rgba(138, 86, 226, 1.0)',
                          'rgba(86, 104, 226, 1.0)',
                      ],
                      borderColor: [
                          'rgba(86, 226, 137, 0.5)',
                          'rgba(104, 226, 86, 0.5)',
                          'rgba(174, 226, 86, 0.5)',
                          'rgba(226, 297, 86, 0.5)',
                          'rgba(226, 137, 86, 0.5)',
                          'rgba(226, 84, 104, 0.5)',
                          'rgba(226, 86, 174, 0.5)',
                          'rgba(207, 86, 226, 0.5)',
                          'rgba(138, 86, 226, 0.5)',
                          'rgba(86, 104, 226, 0.5)',
                      ],
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true,
                              fontSize: 20
                          }
                      }],
                      xAxes: [{
                        ticks: {
                            fontSize: 20
                        }
                      }]
                  },
                  legend:{display: true,labels:{fontSize:20}}
              }
          });
        
          var sPie = jQuery("#seasonPie");
          var msPie = new Chart(sPie, {
              type: 'pie',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      data: [<?php echo implode(",", $this->season_data) ?>],
                      backgroundColor: [
                          'rgba(86, 226, 137, 1.0)',
                          'rgba(104, 226, 86, 1.0)',
                          'rgba(174, 226, 86, 1.0)',
                          'rgba(226, 297, 86, 1.0)',
                          'rgba(226, 137, 86, 1.0)',
                          'rgba(226, 84, 104, 1.0)',
                          'rgba(226, 86, 174, 1.0)',
                          'rgba(207, 86, 226, 1.0)',
                          'rgba(138, 86, 226, 1.0)',
                          'rgba(86, 104, 226, 1.0)',
                      ],
                      hoverBackgroundColor: [
                          'rgba(86, 226, 137, 0.5)',
                          'rgba(104, 226, 86, 0.5)',
                          'rgba(174, 226, 86, 0.5)',
                          'rgba(226, 297, 86, 0.5)',
                          'rgba(226, 137, 86, 0.5)',
                          'rgba(226, 84, 104, 0.5)',
                          'rgba(226, 86, 174, 0.5)',
                          'rgba(207, 86, 226, 0.5)',
                          'rgba(138, 86, 226, 0.5)',
                          'rgba(86, 104, 226, 0.5)',
                      ],
                  }]
              },
              options:{legend:{display: true,labels:{fontSize:20}}},
          });
          
         var aChart = jQuery("#alltimeChart");
          var maChart = new Chart(aChart, {
              type: 'bar',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      label: '# of Places',
                      data: [<?php echo implode(",", $this->alltime_data) ?>],
                      backgroundColor: [
                          'rgba(86, 226, 137, 1.0)',
                          'rgba(104, 226, 86, 1.0)',
                          'rgba(174, 226, 86, 1.0)',
                          'rgba(226, 297, 86, 1.0)',
                          'rgba(226, 137, 86, 1.0)',
                          'rgba(226, 84, 104, 1.0)',
                          'rgba(226, 86, 174, 1.0)',
                          'rgba(207, 86, 226, 1.0)',
                          'rgba(138, 86, 226, 1.0)',
                          'rgba(86, 104, 226, 1.0)',
                      ],
                      borderColor: [
                          'rgba(86, 226, 137, 0.5)',
                          'rgba(104, 226, 86, 0.5)',
                          'rgba(174, 226, 86, 0.5)',
                          'rgba(226, 297, 86, 0.5)',
                          'rgba(226, 137, 86, 0.5)',
                          'rgba(226, 84, 104, 0.5)',
                          'rgba(226, 86, 174, 0.5)',
                          'rgba(207, 86, 226, 0.5)',
                          'rgba(138, 86, 226, 0.5)',
                          'rgba(86, 104, 226, 0.5)',
                      ],
                      borderWidth: 1
                  }]
              },
              options: {
                  scales: {
                      yAxes: [{
                          ticks: {
                              beginAtZero:true,
                              fontSize: 20
                          }
                      }],
                      xAxes: [{
                        ticks: {
                            fontSize: 20
                        }
                      }]
                  },
                  legend:{display: true,labels:{fontSize:20}}
              }
          });
        
          var aPie = jQuery("#alltimePie");
          var maPie = new Chart(aPie, {
              type: 'pie',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      data: [<?php echo implode(",", $this->alltime_data) ?>],
                      backgroundColor: [
                          'rgba(86, 226, 137, 1.0)',
                          'rgba(104, 226, 86, 1.0)',
                          'rgba(174, 226, 86, 1.0)',
                          'rgba(226, 297, 86, 1.0)',
                          'rgba(226, 137, 86, 1.0)',
                          'rgba(226, 84, 104, 1.0)',
                          'rgba(226, 86, 174, 1.0)',
                          'rgba(207, 86, 226, 1.0)',
                          'rgba(138, 86, 226, 1.0)',
                          'rgba(86, 104, 226, 1.0)',
                      ],
                      hoverBackgroundColor: [
                          'rgba(86, 226, 137, 0.5)',
                          'rgba(104, 226, 86, 0.5)',
                          'rgba(174, 226, 86, 0.5)',
                          'rgba(226, 297, 86, 0.5)',
                          'rgba(226, 137, 86, 0.5)',
                          'rgba(226, 84, 104, 0.5)',
                          'rgba(226, 86, 174, 0.5)',
                          'rgba(207, 86, 226, 0.5)',
                          'rgba(138, 86, 226, 0.5)',
                          'rgba(86, 104, 226, 0.5)',
                      ],
                  }]
              },
              options:{legend:{display: true,labels:{fontSize:20}}},
          });
          
          // fetch last games
          if(jQuery("#lastGames").length > 0){
            jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=lastGames&userid="+jQuery("#userid").val()).done(function(data){
             fillLastGames(data);
            });
          }
      } 
    };
</script>


