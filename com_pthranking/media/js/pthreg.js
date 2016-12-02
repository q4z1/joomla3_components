var usernameValid = false;

document.onreadystatechange = function() {
  if (document.readyState === 'complete') {
      jQuery("#pthreg").click(function(event){
          jQuery('#errors').empty();
          usernameValid = false;
          var ret = false;
          event.preventDefault();
          event.stopPropagation();
          console.log("#pthreg clicked.");
          // check Username
          jQuery.get(
            "/index.php?option=com_pthranking&task=webservice&format=raw&pthtype=checkusername&pthusername="+jQuery("#pthranking-username").val()).done(
            function(data){
              console.log("checkusername response="+data);
              var obj = JSON.parse(data);
              if(obj.status != "ok"){
                // an error happened - how to handle it?
              }
              else{
                if(obj.response == true){
                  // username already used - either in player table or in joomla forum user table!
                  // put a hint beside username - username already used!
                  console.log("username already used!");
                  jQuery('#errors').html("<ul class='text-danger'><li>Username is already used!</li></ul>");
                  jQuery('html, body').animate({
                      scrollTop: jQuery("#errors").offset().top - 50
                  }, 500);
                }else{
                  // username is valid - validate the rest
                  usernameValid = true;
                  validate_inputs();
                }
              }
            }
          );
          return ret;
      });
  }
};

function validate_inputs(){
  var valid = true;
  var errors = new Array();
  var email = jQuery("#pthranking-email").val();
  var username = jQuery("#pthranking-username").val();
  var password = jQuery("#pthranking-password").val();
  var password2 = jQuery("#pthranking-password2").val();
  var gender = jQuery("#pthranking-gender option:selected").val();
  var country = jQuery("#pthranking-country option:selected").val();
  // added re-captcha
  var recaptcha_challenge_field = jQuery("input#recaptcha_challenge_field").val();
  var recaptcha_response_field = jQuery("input#recaptcha_response_field").val();
  
  // valid email format?
  var pattern = /^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/i;
  if(!pattern.test(email)){
    valid = false;
    // put a hint beside email input - email format not valid!
    console.log("email address format not valid!");
    errors.push("Email-Address format is not valid!");
  }
  
  // check password
  if(password != password2){
    valid = false;
    // put a hint beside password input - passwords do not match!
    console.log("passwords do not match!");
    errors.push("Passwords do not match!");
  }
  
  if(valid && usernameValid){
    // check email
    jQuery.get(
      "index.php?option=com_pthranking&task=webservice&format=raw&pthtype=checkemail&pthemail="+jQuery("#pthranking-email").val()).done(
      function(data){
        console.log("checkemail response="+data);
        var obj = JSON.parse(data);
        if(obj.status != "ok"){
          // an error happened - how to handle it?
        }
        else{
          if(obj.response == true){
            // email already used - either in player table or in joomla forum user table!
            // put a hint beside username - username already used!
            console.log("email already used!");
            jQuery('#errors').html("<ul class='text-danger'><li>Email-Address is already used!</li></ul>");
            jQuery('html, body').animate({
                scrollTop: jQuery("#errors").offset().top - 50
            }, 500);
          }else{
            // all valid - post data and insert it to database => redirect to email validation site!
            console.log("form data is valid and ready to be inserted into db.");
            jQuery('#errors').html("<ul class='text-success'><li>Saving data into database and sending out a validation email ...</li></ul>");
            jQuery('html, body').animate({
                scrollTop: jQuery("#errors").offset().top - 50
            }, 500);
            var postData = {
              email: email,
              username: username,
              password: btoa(password), // crappy base46 encoding - but we need the clear text passwords on php-server side
              gender: gender,
              country: country,
              recaptcha_challenge_field: recaptcha_challenge_field,
              recaptcha_response_field: recaptcha_response_field,
              submit: true,
            };
            jQuery.post(
              "/index.php?option=com_pthranking&task=webservice&format=raw&pthtype=storeuserdata",
              postData
            ).done(
              function(data){
                console.log("post done - response="+data);
                
                obj = JSON.parse(data);
                if(obj.hasOwnProperty('status') && obj.status == "ok"){
                  // reset form fields
                  jQuery('#pthsignup-form')[0].reset();
                  // show success message
                  jQuery('#errors').html("<ul class='text-success'><li>Registration done! An E-Mail has been sent to you!</li></ul>");
                    jQuery('html, body').animate({
                        scrollTop: jQuery("#errors").offset().top - 50
                    }, 500);
                    // redirect to emailvalidation page
                  window.setTimeout(
                    function(){ window.location.href = '/component/pthranking/?view=emailval' },
                    2500
                  );
                }
                else{
                  jQuery('#errors').html("<ul class='text-danger'><li>"+obj.response+"</li></ul>");
                }
              }
            );

          }
        }
      }
    );
  }else{
    console.log("valid === false && usernameValid === false !");
    var errHtml = "<ul class='text-danger'>";
    jQuery(errors).each(function(i, value){
      errHtml += "<li>"+value+"</li>";
    });
    errHtml += "</ul>";
    jQuery('#errors').html(errHtml);
    jQuery('html, body').animate({
        scrollTop: jQuery("#errors").offset().top - 50
    }, 500);
  }
  
  
  //alert("inputs: e:"+email+"/u:"+username+"/p:"+password+"/p2:"+password2+"/gender:"+gender+"/c:"+country);
}