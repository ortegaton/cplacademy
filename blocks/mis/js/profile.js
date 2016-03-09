

function cleanProfileFrame(stuName){

    // hide loading div
    var mislstat=$GT('mis_loadingstatus');
    mislstat.style.display="none";

    var misprof=document.getElementById('mis_profileview');
    misprof.style.display='';
    if (misprof.contentDocument){
        var idoc=misprof.contentDocument;
    } else {
        var idoc=misprof.contentWindow.document;
    }
    
    // hide document header (because we have that in the main document)
    var head=idoc.getElementById('header');
    head.style.display='none';
    
    // hide nav bar (nav bar has no id so we have to get it by class name)
    var divs=idoc.getElementsByTagName('div');
    for (var d=0; d<divs.length; d++){
        var div=divs[d];
        if (div.className.indexOf('navbar')>-1){                        
            div.style.display='none';
            break;
        }
    }    
    
    // hide user name (because we also have that in the main document)
    var h2s=idoc.getElementsByTagName('h2');        
    for (var n=0; n<h2s.length; n++){
        var h2=h2s[n];            
        if (h2.className=='main' &&  h2.innerHTML==stuName){
            h2.style.display='none';
        }
    }
    
    // Add hide profile frame function to all urls in frame & hide home anchor
    var as=idoc.getElementsByTagName('a');      
    for (var a=0; a<as.length; a++){
        var anc=as[a];
        if (anc.parentNode.className=='homelink'){
            anc.parentNode.style.display='none'; // hide home anchor
        }        
        if (anc.onclick==''){        
            YAHOO.util.Event.addListener(anc, 'click', function(){hidePzProfFrame();});
        }        
    }
    
    // hide profile frame footer -  we don't want a footer in the frame because we have one in the parent frame!
    var footer=idoc.getElementById('footer');
    footer.style.display='none';
    
    
    // remove edit profile tab (its ok for the parents to be able to edit their childs profile but it doesn't play too well under the tabs.) - this is NOT a security issue, it is cosmetic
    // also remove 'roles' tab - not useful to parents
    var as=idoc.getElementsByTagName('a');
    for (var n=0; n<as.length; n++){
        var a=as[n];
        if (a.title=='Edit profile' || a.title=='Roles'){
            var li=a.parentNode;
            var ul=li.parentNode;
            ul.removeChild(li);
        }
    }
}

function hidePzProfFrame(){
    // hide profile frame
    var misprof=$GT('mis_profileview');
    misprof.style.display="none";
    // show loading div
    var mislstat=$GT('mis_loadingstatus');
    mislstat.style.display="";
    
}

YAHOO.util.Event.addListener(window, 'load', function(){    
    var misprof=document.getElementById('mis_profileview');
    try{
        cleanProfileFrame(mis_stuname);    
    } catch (e){
    }
    YAHOO.util.Event.addListener(misprof, 'load', function(){cleanProfileFrame(mis_stuname);});
});

 
    