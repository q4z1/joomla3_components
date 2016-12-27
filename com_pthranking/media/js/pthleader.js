var page=1;
var size=50;
var max_page = 0;


document.onreadystatechange = function() {
  if (document.readyState === 'complete') {
    jQuery('#btn-search').click(
      function(event){
        var ret = false;
        event.preventDefault();
        event.stopPropagation();
        var username = jQuery('#username').val();
        if(username == "") return ret;
        jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=rankingtable&username="+username+"&searchplayer=1").done(
          function(data){
            display_search_result(data);
          }                  
        );
        return ret;
      }                          
    );
    
    jQuery('#but_next').click(
      function(event){
        var ret = false;
        event.preventDefault();
        event.stopPropagation();
        loadnext();
        return ret;
      }                          
    );
    
    jQuery('#but_prev').click(
      function(event){
        var ret = false;
        event.preventDefault();
        event.stopPropagation();
        loadprev();
        return ret;
      }                          
    );
    
    
    // @XXX: due to weird loading of jquery in joomla - load this jquery plugin when dom is loaded
    jQuery.getScript("/media/com_pthranking/js/jquery.simplePagination.js?ts=20161207_0355", function(){
      loadpage(page,size);
    });
    
    jQuery.getScript("/media/com_pthranking/js/jquery.easy-autocomplete.min.js?ts=20161221_0355", function(){
      // autocomplete user search
      var autocOptions = {
        url: function(uname) {
          return "/component/pthranking/?view=webservice&format=raw&pthtype=autocompleteuser&username=" + uname;
        },
        getValue: "value",
        theme: "square",
        list: {
          maxNumberOfElements: 10,
          match: {
            enabled: true
          },
          onChooseEvent: function(){
            var username = jQuery('#username').val();
            if(username == "") return ret;
            jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=rankingtable&username="+username+"&searchplayer=1").done(
              function(data){
                display_search_result(data);
              }                  
            );
          },
        }
      };
      jQuery("#username").easyAutocomplete(autocOptions);
      jQuery('#username').keypress(function (e) {
        // check enter-key
        if (e.which == 13) {
          var username = jQuery('#username').val();
          if(username == "") return false;
          jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=rankingtable&username="+username+"&searchplayer=1").done(
            function(data){
              display_search_result(data);
            }                  
          );
        }
      });
    });
  } 
};

function display_search_result(data){
  //console.log("data="+data);
  // check for valid result
  if(data == "[]") return; // username not found
  objects = JSON.parse(data);
  var pagination = objects.pagination;
  page = pagination.page;
  max_page = pagination.max_page;
  jQuery("#pagenum").html("page: "+page+ " of " + max_page);
  
  buildHtmlTable('#ranking_table',objects);
}

// TODO: change webservice to calculate points
// TODO: error if empty
// TODO: page from GET parameter
// TODO: enter page number possible
// TODO: variable page sizes (maybe offer 25,50,100)
// TODO: css, design!!
// TODO: make webservice tell total number of pages => see result data porperty pagination
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
  var start=(pagenumber-1)*pagesize+1;
  jQuery.get("?option=com_pthranking&task=webservice&format=raw&pthtype=rankingtable&start="+start+"&size="+pagesize).done(
    function(data){
    var obj = JSON.parse(data);
    buildHtmlTable('#ranking_table',obj);
  });

}

// Builds the HTML Table out of myList.
titlerow="<tr><th>Rank</th><th>Name</th><th>Average Points</th><th>Games (Season)</th><th>Score</th><th></th><th></th></tr>"
function buildHtmlTable(selector,data) {
    var columns=["rank","username","average_points","season_games","final_score", "gender", "country"];
    jQuery(selector).empty();
    jQuery(selector).html(titlerow);
    max_page = data.pagination.max_page;
    page = data.pagination.page;
    var myList = data.table;
    for (var i = 0 ; i < myList.length ; i++) {
        var row$ = jQuery('<tr/>');
        for (var colIndex = 0 ; colIndex < columns.length ; colIndex++) {
            var cellValue = myList[i][columns[colIndex]];
            if (cellValue == null) { cellValue = ""; }
            if(columns[colIndex]=="username") {
                var profilelink="/component/pthranking/?view=pthranking&layout=profile&userid="+myList[i]["userid"];
                row$.append(jQuery('<td/>').html("<a href=\""+profilelink+"\">"+cellValue+"</a>")); 
            }else {
                row$.append(jQuery('<td/>').html(cellValue));
            }
        }
        jQuery(selector).append(row$);
    }
    var JTooltips = new Tips($$('.hasTip'), 
       { maxTitleChars: 50, fixed: false});
    if (myList.length==0) {
      jQuery(selector).append("<tr><td colspan='7'>No data found</td></tr>");
    }
    
    if(jQuery('span.text-danger').length > 0){
      jQuery('html, body').animate({
          scrollTop: jQuery("span.text-danger").offset().top - 50
      }, 2000);
    }
    
    build_pagination();
}

function build_pagination(){
  jQuery('.pagination a').each(function(i, item){jQuery(item).unbind('click');}); // unbind click events
  jQuery('.pagination').pagination({
        pages: max_page,
        currentPage: page,
        displayedPages: 7,
        selectOnClick: false,
        onPageClick: function(pageNum){loadpage(pageNum, size);}
  });
  jQuery('.pagination a').each(function(i, item){
    jQuery(item).click(
      function(event){
        var ret = false;
        event.preventDefault();
        event.stopPropagation();
        return ret;
      }
    );
  });
}

