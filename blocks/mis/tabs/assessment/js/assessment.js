var assessment=function(){
    var me={
        constructor:function(){
            // Apply click events to studentexams
            YAHOO.util.Event.addListener(window, 'load', function(){me.applyClickEvents()});  
        },
        applyClickEvents:function(){
            // Get unordered list
            var stuexams=$GT('studentexams');
            
            if (!stuexams){
                return;
            }
            
            // Apply events to each item of unordered list            
            var lis=stuexams.getElementsByTagName('li');
            if (lis){
                for (var l=0; l<lis.length; l++){
                    var li=lis[l];
                    var clickhandle=$GT('clickhandle~'+li.id);
                    me.add_cb_click_examitem(clickhandle, li);
                }
            }
        },
        
        // Add call back to click event of list item
        add_cb_click_examitem:function(clickhandle, li){
            YAHOO.util.Event.addListener(clickhandle, 'click', function(){me.cb_click_examitem(clickhandle, li)});
        },
        
        // Call back for click event of list item
        cb_click_examitem:function(clickhandle, li){
            var examid=li.id.split('~')[1];

            // get or create item to display exam data
            var ulli=me.getexamitem_subul(li);
            var ul=ulli.ul;
            var examli=ulli.li;
            
            // if list item is collapsed then expand and get the examdetails.
            if (YAHOO.util.Dom.hasClass(li, 'collapsed')){
                // change class to expanded
                YAHOO.util.Dom.replaceClass(li, 'collapsed', 'expanded');
                YAHOO.util.Dom.replaceClass(clickhandle, 'collapsed', 'expanded');
                ul.style.display='';
                examli.style.display='';  
                // I HATE IE- don't use it, its rubbish, use Firefox instead - go on, spread the word!
                if (GTLib.Browser.ie){
                    // Make IE play nice (hide list then show otherwise list is rendered weird)
                    $GT('studentexams').style.display='none';                 
                    $GT('studentexams').style.display='';                  
                }
              
                examli.innerHTML='<div class="ajaxloading">Please wait, loading data...</div>';
                
                // get exam details
                me.REQ_examdetails(examid, examli);
            } else {
                // change class to collapsed
                YAHOO.util.Dom.replaceClass(li, 'expanded', 'collapsed');
                YAHOO.util.Dom.replaceClass(clickhandle, 'expanded', 'collapsed');
                ul.style.display='none';
                examli.style.display='none';   
                // I HATE IE- don't use it, its rubbish, use Firefox instead - go on, spread the word!
                if (GTLib.Browser.ie){
                    // Make IE play nice (hide list then show otherwise list is rendered weird)
                    $GT('studentexams').style.display='none';                 
                    $GT('studentexams').style.display='';                  
                }
            }
        },
        
        getexamitem_subul:function(li){
            var uls=li.getElementsByTagName('ul');                
            if (uls.length==0){
                // no un-ordered list exists under li, create it
                var newul=createEl('ul');
                var examli=createEl('li', {'class':'examdata'});     
                newul.appendChild(examli);
                li.appendChild(newul);
            } else {
                // use existing list item under li                
                var lis=uls[0].getElementsByTagName('li');
                var examli=lis[0];
            }
            return {ul:uls[0], li:examli};
        },
        
        // Request exam details for specific examid
        REQ_examdetails:function(examid, examli){
            var reqURL=misblockbase+'/tabs/assessment/ajax/examdatatable.php';
            var sendstr='examid='+examid+'&dataset='+$GTF('datasets');
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_examdetails(o, examid, examli)}, failure:function(){alert('failed '+reqURL)}});        
        },
        
        // Received exam details
        REC_examdetails:function(o, examid, examli){
            examli.innerHTML=o.responseText;
        }
    }
    me.constructor();
    return me;
}

var assessment_init=new assessment();