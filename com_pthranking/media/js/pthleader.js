var page=1;
var size=50; 


document.onreadystatechange = function() {
  if (document.readyState === 'complete') {
//      alert('Hello earth');
//     start=(page-1)*size+1;
//     jQuery.get("?option=com_pthranking&task=webservice&format=raw&pthtype=rankingtable&start="+start+"&size="+size).done(
//        function(data){
//          var obj = JSON.parse(data);
//          jQuery('#ranking_table').html(data);
//          buildHtmlTable('#ranking_table',obj);
// 
//        }
//      );
    loadpage(page,size);
  } // end if
};

// TODO: change webservice to calculate points
// TODO: error if empty
// TODO: page from GET parameter
// TODO: enter page number possible
// TODO: variable page sizes (maybe offer 25,50,100)
// TODO: css, design!!
// TODO: make webservice tell total number of pages
// TODO: search by player

function loadprev(){
  page-=1;
  if(page<1) page=1;
  loadpage(page,size);
}

function loadnext(){
  page+=1;
  loadpage(page,size);
}

function loadpage(pagenumber,pagesize) {
  if (pagenumber<1) pagenumber=1;
  jQuery("#pagenum").html("page: "+pagenumber);
  var start=(pagenumber-1)*pagesize+1;
  jQuery.get("?option=com_pthranking&task=webservice&format=raw&pthtype=rankingtable&start="+start+"&size="+pagesize).done(
    function(data){
    var obj = JSON.parse(data);
    buildHtmlTable('#ranking_table',obj);
  });

}

// document.getElementById("but_prev").onclick = function() {
//   page-=1;
//   if(page<1) page=1;
//   loadpage(page,size);
// };
// 
// document.getElementById("but_next").onclick = function() {
//   page+=1;
// //   if(page<1) page=1;
//   loadpage(page,size);
// };
// 


// Builds the HTML Table out of myList.
titlerow="<tr><th>Rank</th><th>Name</th><th>Average Points</th><th>Games (Season)</th><th>Score</th></tr>"
function buildHtmlTable(selector,myList) {
    var columns=["rank","username","average_points","season_games","final_score"];
    jQuery(selector).empty();
    jQuery(selector).html(titlerow);

    for (var i = 0 ; i < myList.length ; i++) {
        var row$ = jQuery('<tr/>');
        for (var colIndex = 0 ; colIndex < columns.length ; colIndex++) {
            var cellValue = myList[i][columns[colIndex]];

            if (cellValue == null) { cellValue = ""; }

            row$.append(jQuery('<td/>').html(cellValue));
        }
        jQuery(selector).append(row$);
    }
    if (myList.length==0) {
      jQuery(selector).append("<tr><td colspan=5>No data found</td></tr>");
    }
}

