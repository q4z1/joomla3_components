document.onreadystatechange = function() {
  if (document.readyState === 'complete') {
    if(jQuery("#pthdel").length > 0){
        jQuery("#pthdel").click(function(e){
              var ret = false;
              event.preventDefault();
              event.stopPropagation();
              //console.log("del button clicked.");
              jQuery("#deleteModal").modal();
        });
        jQuery("#pthDoDel").click(function(e){
              var ret = false;
              event.preventDefault();
              event.stopPropagation();
              console.log("doing deletion");
              jQuery("#deleteModal").modal("hide");
                jQuery.get("/component/pthranking/?view=webservice&format=raw&pthtype=delAcc&email="+jQuery("#email").val()).done(function(data){
                    console.log("data="+data);
                    if(data == "ok"){
                        jQuery("#pthdel").remove();
                        jQuery(".pthdel").each(function(i, item){
                            jQuery(item).remove();
                        });
                        jQuery('#errors').html("<ul class='text-success'><li>Account successfully deleted!</li></ul>");
                    }else{
                        jQuery('#errors').html("<ul class='text-danger'><li>There was an error - please contact webmaster@pokerth.net!</li></ul>");
                    }
                });
        });
    }
  }
  
}