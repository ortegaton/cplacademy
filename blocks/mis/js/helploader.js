/*<![CDATA[*/
var reqHelp;
function helpDraw(helpfile) {      
	if(window.XMLHttpRequest) {
                reqHelp = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
                reqHelp = new ActiveXObject("Microsoft.XMLHTTP");
        }
        var urlCal = "help/" + helpfile +".htm";
        reqHelp.open("GET", urlCal, true);
        reqHelp.onreadystatechange = callbackHelp;
        reqHelp.send(null);        
}


function callbackHelp() {        
    if(reqHelp.readyState == 4) {
         if(reqHelp.status == 200) {
           
            var buttons=[
		{name:'OK', functCode:function(){dg_help.CloseDialog()}}];
		var dg_help=new Dialog("Help",reqHelp.responseText,{buttons:buttons});
		dg_help.lightBox=true;
		
		dg_help.h= 450;
		dg_help.w= 450;
		dg_help.Init();	
          } else {
               alert("There was a problem retrieving the help data");
          }
     }
}	
/*]]>*/  