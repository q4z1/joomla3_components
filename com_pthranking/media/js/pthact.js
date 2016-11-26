document.onreadystatechange = () => {
  if (document.readyState === 'complete') {
      jQuery("#pthact").click(function(event){
          jQuery('#errors').empty();
          var ret = false;
          event.preventDefault();
          event.stopPropagation();
          console.log("#pthact clicked.");
          // check password
          jQuery.get(
            "/index.php?option=com_pthranking&task=webservice&format=raw&pthtype=checkpassword&pthpassword="+btoa(jQuery("#pthranking-password").val())).done(
            function(data){
              console.log("checkpassword response="+data);
              var obj = JSON.parse(data);
              if(obj.status != "ok"){
                // an error happened - how to handle it?
              }
              else{
                if(obj.response !== true){
                  // password does not match!
                  // put a hint - password does not match!
                  console.log("password does not match!");
                  jQuery('#errors').html("<ul class='text-danger'><li>Password does not match!</li></ul>");
                  jQuery('html, body').animate({
                      scrollTop: jQuery("#errors").offset().top - 50
                  }, 500);
                }else{
                    // password is valid - create game account
                    var postData = {
                      password: btoa(jQuery("#pthranking-password").val()), // crappy base46 encoding - but we need the clear text passwords on php-server side
                      gender: jQuery("#pthranking-gender option:selected").val(),
                      country: jQuery("#pthranking-country option:selected").val(),
                      submit: true,
                    };
                    jQuery.post(
                      "/index.php?option=com_pthranking&task=webservice&format=raw&pthtype=doforumaccounttransfer",
                      postData
                    ).done(
                      function(data){
                        console.log("post done - response="+data);
                        // reset form fields
                        jQuery('#pthactivategame-form')[0].reset();
                        // show success message
                        jQuery('#errors').html("<ul class='text-success'><li>Your account is now activated for the game!</li></ul>");
                          jQuery('html, body').animate({
                              scrollTop: jQuery("#errors").offset().top - 50
                          }, 500);
                          // @TODO: remove the form
                       }
                    );
                }
              }
            }
          );
          return ret;
      });
  }
};