

function fillLastGames(data){
    if(data == "[]"){
      jQuery("#lastGames").html("<p>No Games played!</p>");  
    }
    else{
        var objects = JSON.parse(data);
        var target = jQuery("#lastGames");
        var table = "<table class='table table-striped table-hover table-bordered'>";
        table += "<th>Game Table</th><th>Game Start</th><th>Game End</th><th>Place</th>";
        jQuery(objects).each(function(i, obj){
                table += "<tr><td><a href='#' __data_id='"+obj.game_idgame+"' class='tableInfo'>" + obj.name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + "</a></td>"
                    + "<td>" +obj.start_time + "</td>"
                    + "<td>" + obj.end_time + "</td>"
                    + "<td>" + obj.place + "</td>"
                    + "</tr>";
            }
        );
        table += "</table>";
        jQuery("#lastGames").html(table);
        
        // bind click events
        jQuery('a.tableInfo').each(function(i, item){
            jQuery(item).click(function(event){
                var ret = false;
                event.preventDefault();
                event.stopPropagation();
                var gameId = jQuery(item).attr('__data_id');
                console.log("fetch game "+gameId+" ...");
                jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=gameInfo&gameid="+gameId).done(function(data){
                 showGameModal(data);
                });
                return ret;
            });    
        });
    }
}

function fillMoreGames(data){
    if(data == "[]"){
      jQuery("#moreGames").html("<p>No more Games played!</p>");  
    }
    else{
        var objects = JSON.parse(data);
        var target = jQuery("#moreGames");
        var table = "<h4>More/All Games</h4><table class='table table-striped table-hover table-bordered'>";
        table += "<th>Game Table</th><th>Game Start</th><th>Game End</th><th>Place</th>";
        jQuery(objects).each(function(i, obj){
                table += "<tr><td><a href='#' __data_id='"+obj.game_idgame+"' class='tableInfo'>" + obj.name.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;') + "</a></td>"
                    + "<td>" +obj.start_time + "</td>"
                    + "<td>" + obj.end_time + "</td>"
                    + "<td>" + obj.place + "</td>"
                    + "</tr>";
            }
        );
        table += "</table>";
        jQuery("#moreGames").html(table);
        jQurey("#moreGames").css("color", "inherit!important");
        
        // bind click events
        jQuery('a.tableInfo').each(function(i, item){
            jQuery(item).click(function(event){
                var ret = false;
                event.preventDefault();
                event.stopPropagation();
                var gameId = jQuery(item).attr('__data_id');
                console.log("fetch game "+gameId+" ...");
                jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=gameInfo&gameid="+gameId).done(function(data){
                 showGameModal(data);
                });
                return ret;
            });    
        });
    }
}

function showGameModal(data){
    var entries = JSON.parse(data);
    var modal = jQuery("#tableModal");
    var label = "Game Name: " + entries.gamename.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        + " ("+entries.start_time+" - "+entries.end_time+"):";
    jQuery("#tableModalLabel").text(label);
    var table = "<table class='table table-striped table-hover table-bordered'>";
    table += "<th>Place</th><th>Player</th>";
    jQuery(entries.places).each(function(i, obj){
        table += "<tr><td>" + obj.place + "</td>"
            + "<td><a href='/component/pthranking/?view=pthranking&layout=profile&userid=" + obj.userid + "' target='_blank'>" + obj.username + "</a></td>"
            + "</tr>";
    });
    table += "</table>"
    jQuery("#tableModalBody").html(table);
    jQuery(modal).modal();
}