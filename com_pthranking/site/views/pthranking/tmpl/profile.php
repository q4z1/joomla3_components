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
$document->addScript(JUri::root() . 'media/com_pthranking/js/pthprofile.js?tx=20170228_1823'); // chart.js is already included by template
$document->addStyleSheet(JUri::root() . 'media/com_pthranking/css/pthranking.css?tx=20170228_1816');

//$current = $this->all_seasons[count($this->all_seasons)-2];
//$alltime = $this->all_seasons[count($this->all_seasons)-1];
if($this->userexists){
 $seasons = array();
 $i = 0;
 $current = count($this->all_seasons) - 2;
 $alltime = count($this->all_seasons) - 1;
 foreach($this->all_seasons as $season => $data){
  if($i != $current && $i != $alltime) $seasons[$season] = array("quart" => $season, "data" => $data);
  elseif($i == $current) $seasons["current"] = array("quart" => $season, "data" => $data);
  elseif($i == $alltime) $seasons["alltime"] = array("quart" => $season, "data" => $data);
  $i++;
 }
}

//echo("<pre>".var_export($seasons, true)."</pre>");
?>
<input type="hidden" name="userid" id="userid" value="<?php echo $this->userid?>" />
<div class="rt-flex-container">
    <div class="rt-grid-12">
        <div>
            <?php if($this->userexists): ?>
            <h1>Profile: <?php echo $this->usernameExt?></h1>
            <h3>Ranking information about current season (<?php echo $seasons["current"]["quart"] ?>):</h3>
            <?php echo $this->basicinfo_html; ?>
            <hr />
            <h3>Statistics for current season (<?php echo $seasons["current"]["quart"] ?>):</h3>
            <?php echo $this->seasonpiedata; ?>
            <canvas id="pie<?php echo $seasons["current"]["quart"] ?>" width="100" height="100" class="canvas-holder half"></canvas>
            <canvas id="chart<?php echo $seasons["current"]["quart"] ?>" width="150" height="100" class="canvas-holder half"></canvas>
            <div style="clear: left;"></div>
            <hr />
            <h3>Statistics for all time:</h3>
            <?php if($seasons["alltime"]["data"]["sum"] == 0): echo "n/a"; ?>
            <?php else: ?>
            <?php
                $ret="<table class='table table-striped table-hover table-bordered'>\n"; // maybe removej
                $row="<tr><td>Place:</td>";
                $key="place";
                foreach($seasons["alltime"]["data"]["place"] as $key => $entry) $row.="<td>".$key."</td>";
                $row .= "<td>sum</td>";
                $row.="</tr>\n";
                $ret.=$row;
        
                $row="<tr><th>Games:</th>";
                $key="count";
                foreach($seasons["alltime"]["data"]["place"] as $key => $entry) $row.="<td>".$entry."</td>";
                $row .= "<td>".$seasons["alltime"]["data"]["sum"]."</td>";
                $row.="</tr>\n";
                $ret.=$row;
        
                $row="<tr><td>Percent:</td>";
                $key="percent";
                foreach($seasons["alltime"]["data"]["place"] as $key => $entry){
                    $percent = $entry*100/$seasons["alltime"]["data"]["sum"];
                    $row.="<td>".sprintf("%.1f %%",$percent)."</td>";
                }
                $row .= "<td>100.00 %</td>";
                $row.="</tr>\n";
                $ret.=$row;
                $ret.="</table>";
                echo $ret;
            ?>
            <canvas id="alltimePie" width="100" height="100" class="canvas-holder half"></canvas>
            <canvas id="alltimeChart" width="150" height="100" class="canvas-holder half"></canvas>
            <div style="clear: left;"></div>
            <hr />
            <h3>Last 20 Games played:</h3>
            <div id="lastGames"></div>
            <div id="moreGames">
             <span style="text-decoration: underline; cursor: pointer; color: #548612">Show more/all games</span>
            </div>
            <?php endif; ?>
            <hr />
            <h2>Historical Seasons:</h2>
            <?php foreach($seasons as $season => $data): ?>
            <?php if($season == "current" || $season == "alltime") continue; ?>
            <h4>Statistics for <?php echo $season; ?>:</h3>
            <?php if($data["data"]["data"][10]["count"] == 0): echo "n/a"; continue; endif;?>
            <?php
                $ret="<table class='table table-striped table-hover table-bordered'>\n"; // maybe removej
                $row="<tr><td>Place:</td>";
                $key="place";
                foreach($data["data"]["data"] as $key => $entry) $row.="<td>".$entry["place"]."</td>";
                //$row .= "<td>sum</td>";
                $row.="</tr>\n";
                $ret.=$row;
        
                $row="<tr><th>Games:</th>";
                $key="count";
                foreach($data["data"]["data"] as $key => $entry) $row.="<td>".$entry["count"]."</td>";
                //$row .= "<td>".$data["data"]["data"][10]["count"]."</td>";
                $row.="</tr>\n";
                $ret.=$row;
        
                $row="<tr><td>Percent:</td>";
                $key="percent";
                foreach($data["data"]["data"] as $key => $entry){
                 if($data["data"]["data"][10]["count"] > 0){
                    $percent = $entry["count"]*100/$data["data"]["data"][10]["count"];
                 }else{
                  $percent = 0;
                 }
                 $row.="<td>".sprintf("%.1f %%",$percent)."</td>";
                }
                //$row .= "<td>100.00 %</td>";
                $row.="</tr>\n";
                $ret.=$row;
                $ret.="</table>";
                echo $ret;
            ?>

            <canvas id="pie<?php echo $season ?>" width="100" height="100" class="canvas-holder half"></canvas>
            <canvas id="chart<?php echo $season ?>" width="150" height="100" class="canvas-holder half"></canvas>
            <div style="clear: left;"></div>
            <?php endforeach; ?>
<script>
  document.onreadystatechange = function() {
      if (document.readyState === 'complete') {
       
       // @XXX: bugfix for missing bootstrap on leaderboard short urls
       if (typeof jQuery("#tableModal").modal !== "function") {
         console.log("loading missing bootstrap...");
         jQuery.getScript( "/media/jui/js/bootstrap.min.js" )
          .done(function( script, textStatus ) {
            console.log( textStatus );
          });
       }
       
         var sChart = jQuery("#chart"+'<?php echo $seasons["current"]["quart"] ?>');
          var msChart = new Chart(sChart, {
              type: 'bar',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      label: '# of Places',
                      data: [<?php echo str_replace("d=", "", $seasons["current"]["data"]["url"][0]) ?>],
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
        
          var aPie = jQuery("#pie"+'<?php echo $seasons["current"]["quart"] ?>');
          var asPie = new Chart(aPie, {
              type: 'pie',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      data: [<?php echo str_replace("d=", "", $seasons["current"]["data"]["url"][0]) ?>],
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
                      data: [<?php echo implode(",", $seasons["alltime"]["data"]["place"]) ?>],
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
                      data: [<?php echo implode(",", $seasons["alltime"]["data"]["place"]) ?>],
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
          <?php $i = 0; ?>
          <?php foreach($seasons as $key => $season): ?>
          <?php $i++; ?>
          <?php echo "console.log('season=".$key."');"; ?>
          <?php if($key != "current" && $key != "alltime"): ?>
          var sChart<?php echo $i ?> = jQuery("#chart"+'<?php echo $season["quart"] ?>');
          var msChart<?php echo $i ?> = new Chart(sChart<?php echo $i ?>, {
              type: 'bar',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      label: '# of Places',
                      data: [<?php echo str_replace("d=", "", $season["data"]["url"][0]) ?>],
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
        
          var aPie<?php echo $i ?> = jQuery("#pie"+'<?php echo $season["quart"] ?>');
          var asPie<?php echo $i ?> = new Chart(aPie<?php echo $i ?>, {
              type: 'pie',
              data: {
                  labels: ["1st", "2nd", "3rd", "4th", "5th", "6th", "7th", "8th", "9th", "10th"],
                  datasets: [{
                      data: [<?php echo str_replace("d=", "", $season["data"]["url"][0]) ?>],
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
          <?php endif; ?>
          <?php endforeach; ?>
          
          // fetch last games
          if(jQuery("#lastGames").length > 0){
            jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=lastGames&userid="+jQuery("#userid").val()).done(function(data){
             fillLastGames(data);
            });
          }
          
          if(jQuery("#moreGames").length > 0){
           
            jQuery("#moreGames").click(function(event){
                var ret = false;
                event.preventDefault();
                event.stopPropagation();
                jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=lastGames&more=tru&userid="+jQuery("#userid").val()).done(function(data){
                 fillMoreGames(data);
                });
                return ret;
            }); 
          }
      } 
    };
</script>       
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



