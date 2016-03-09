var reqCal;
var reqGraph;
var reqAttTable;
function monthDraw(month,year) {
       
        if(window.XMLHttpRequest) {
                reqCal = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
                reqCal = new ActiveXObject("Microsoft.XMLHTTP");
        }
        var urlCal = "tabs/attendance/lib/attCalendar.php?month="+month+"&year="+year;
        reqCal.open("GET", urlCal, true);
        reqCal.onreadystatechange = callbackCal;
        reqCal.send(null);        
        // GT MOD - set calnder loading status
        var obj = document.getElementById("calendar");
        setAJAXStatusLoading(obj, 'loading calendar, please wait...');
        
        if(window.XMLHttpRequest) {
               reqGraph = new XMLHttpRequest();
        } else if(window.ActiveXObject) {
               reqGraph = new ActiveXObject("Microsoft.XMLHTTP");
        }            
        var urlGraph = "tabs/attendance/lib/attChart.php?month="+month+"&year="+year;
        reqGraph.open("GET", urlGraph, true);
	reqGraph.onreadystatechange = callbackGraph;
        reqGraph.send(null);
        // GT MOD - set chart loading status
        var obj = document.getElementById("graph");
        setAJAXStatusLoading(obj, 'loading graph, please wait...');       
        
        
        if(window.XMLHttpRequest) {
	    reqAttTable = new XMLHttpRequest();
	} else if(window.ActiveXObject) {
	    reqAttTable = new ActiveXObject("Microsoft.XMLHTTP");
	}            
	var urlAttTable = "tabs/attendance/lib/attTable.php?month="+month+"&year="+year;
	reqAttTable.open("GET", urlAttTable, true);
	reqAttTable.onreadystatechange = callbackAttTable;
	reqAttTable.send(null);
    // GT MOD - set chart loading status
    var obj = document.getElementById("attTable");
     setAJAXStatusLoading(obj, 'loading attendance table, please wait...');     
        
}

function callbackCal() {        
        objCal = document.getElementById("calendar");
              
		if(reqCal.readyState == 4) {
                if(reqCal.status == 200) {
                        response = reqCal.responseText;
                        objCal.innerHTML = response;
                      
                } else {
                        alert("There was a problem retrieving the data:\n" + reqCal.statusText);
                }
        }
}
function callbackGraph() {        
        objGraph = document.getElementById("graph");
        if(reqGraph.readyState == 4) {
                if(reqGraph.status == 200) {
                        response = reqGraph.responseText;
                        objGraph.innerHTML = response;
                } else {
                        alert("There was a problem retrieving the data:\n" + reqGraph.statusText);
                }
        }
}

function callbackAttTable() {        
        objAttTable = document.getElementById("attTable");
        if(reqAttTable.readyState == 4) {
                if(reqAttTable.status == 200) {
                        response = reqAttTable.responseText;
                        objAttTable.innerHTML = response;
                } else {
                        alert("There was a problem retrieving the data:\n" + reqAttTable.statusText);
                }
        }
}

// GT MOD
// Purpose: Set status to loading at point of AJAX request
//
function setAJAXStatusLoading(obj, msg){
    var msg=msg ? msg : 'loading...';
    obj.innerHTML='<div class="ajaxloading">'+msg+'</div>';
}
