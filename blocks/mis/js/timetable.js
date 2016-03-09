var timetable=function(){
    var me={
        //
        // Constructor
        //
        constructor:function(){            
            YAHOO.util.Event.addListener(window, 'load', function(){me.applyClickEvents()});
        },
        
        //
        // Apply click events to timetable events
        //
        applyClickEvents:function(){     


            var ttevs=YAHOO.util.Dom.getElementsByClassName('event', 'div', $GT('timetables'));
            for (t=0; t<ttevs.length; t++){
                var evdiv=ttevs[t];
                // Disabled for now!
                //YAHOO.util.Event.addListener(ttevs[t], 'click', function(){me.dgEvent(evdiv)});
            }      
        },        
        
        //
        // Dialog - show event
        //
        dgEvent:function(evdiv){
            var slotid=evdiv.id.replace('sid_','');
            var buttons=[
                {name:'OK', functCode:function(){dg.CloseDialog();}}
            ]
            var dialogtitle='Event Information';
            var dg=new Dialog(dialogtitle,'<div id="evi_'+slotid+'"></div>', {buttons:buttons,w:500,h:410, lightBox:true});
            dg.Init();
            dg.StatusLoading('Please wait...');
            me.REQ_eventInfo(slotid, dg);
        },
        
        //
        // Request event details
        //
        REQ_eventInfo:function(slotid, dg){
            var sendstr='slotid='+slotid;
            sendstr+='&sesskey='+mdlsessid;
            var reqURL=misblockbase+'/tabs/timetable/ajax/AJAX_tteventdetails.php';
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_eventInfo(o, dg)}, failure:function(o){alert('failed '+reqURL)}});            
        },
        
        //
        //
        //
        REC_eventInfo:function(o, dg){
            alert (o.responseText);
        }
        
        
    };
    
            
    
    me.constructor();
    return (me);
}

var timetableinst=new timetable();


 