var usernameValid = false;

document.onreadystatechange = () => {
  if (document.readyState === 'complete') {
     alert('Hello earth');
     jQuery.get("?option=com_pthranking&task=webservice&format=raw&pthtype=rankingtable").done(
       function(data){
         console.log("data = "+data);
//          alert(data);
         var obj = JSON.parse(data);
//          jQuery('#ranking_table').html(data);
         buildHtmlTable('#ranking_table',obj);


       }
     );

 
        
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


// var myList = [{"name" : "abc", "age" : 50},
//               {"age" : "25", "hobby" : "swimming"},
//               {"name" : "xyz", "hobby" : "programming"}];

// Builds the HTML Table out of myList.
function buildHtmlTable(selector,myList) {
    var columns = addAllColumnHeaders(myList, selector);

    for (var i = 0 ; i < myList.length ; i++) {
        var row$ = jQuery('<tr/>');
        for (var colIndex = 0 ; colIndex < columns.length ; colIndex++) {
            var cellValue = myList[i][columns[colIndex]];

            if (cellValue == null) { cellValue = ""; }

            row$.append(jQuery('<td/>').html(cellValue));
        }
        jQuery(selector).append(row$);
    }
}

// Adds a header row to the table and returns the set of columns.
// Need to do union of keys from all records as some records may not contain
// all records
function addAllColumnHeaders(myList, selector)
{
    var columnSet = [];
    var headerTr$ = jQuery('<tr/>');

    for (var i = 0 ; i < myList.length ; i++) {
        var rowHash = myList[i];
        for (var key in rowHash) {
            if (jQuery.inArray(key, columnSet) == -1){
                columnSet.push(key);
                headerTr$.append(jQuery('<th/>').html(key));
            }
        }
    }
    jQuery(selector).append(headerTr$);

    return columnSet;
}

