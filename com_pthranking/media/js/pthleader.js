var usernameValid = false;

document.onreadystatechange = () => {
  if (document.readyState === 'complete') {
     alert('Hello earth');
//       jQuery("#pthreg").click(function(event){
//           jQuery('#errors').empty();
//           usernameValid = false;
//           var ret = false;
//           event.preventDefault();
//           event.stopPropagation();
//           console.log("#pthreg clicked.");
//           // check Username
//           jQuery.get(
//             "/index.php?option=com_pthranking&task=webservice&format=raw&pthtype=checkusername&pthusername="+jQuery("#pthranking-username").val()).done(
//             function(data){
//               console.log("checkusername response="+data);
//               var obj = JSON.parse(data);
//               if(obj.status != "ok"){
//                 // an error happened - how to handle it?
//               }
//               else{
//                 if(obj.response == true){
//                   // username already used - either in player table or in joomla forum user table!
//                   // put a hint beside username - username already used!
//                   console.log("username already used!");
//                   jQuery('#errors').html("<ul class='text-danger'><li>Username is already used!</li></ul>");
//                   jQuery('html, body').animate({
//                       scrollTop: jQuery("#errors").offset().top - 50
//                   }, 500);
//                 }else{
//                   // username is valid - validate the rest
//                   usernameValid = true;
//                   validate_inputs();
//                 }
//               }
//             }
//           );
//           return ret;
//       });
  }
};

