YUI().use('yui2-event','yui2-dom','yui2-dragdrop', function(Y){
var YAHOO=Y.YUI2;
var manageassessments=function(){
    var me={
        //
        // Constructor
        //
        constructor:function(){
            // Apply click events to rewards menu items
            YAHOO.util.Event.addListener(window, 'load', function(){me.applyClickEvents()});         
        },
        
        //
        // Get all dataset objects and apply click events
        //
        applyClickEvents:function(){            
            var datasets=YAHOO.util.Dom.getElementsByClassName('setid', 'a', $GT('manageassessments'));
            for (var d=0; d<datasets.length; d++){
                var dsel=datasets[d];                
                me.applyClick_dataSet(dsel);
            }
            YAHOO.util.Event.addListener($GT('saveassessmentconfig'), 'click', function(){me.save()});
        },
        
        //
        // Apply click event for specific data set element
        //
        applyClick_dataSet:function(dsel){
            YAHOO.util.Event.addListener(dsel, 'click', function(){me.toggleDataSet(dsel)});
        },
        
        //
        // Toggle Data Set - open / close
        //
        toggleDataSet:function(dsel){
            var setid=dsel.id.replace('sid~','');
            
            // if collapsed then expanded
            if (YAHOO.util.Dom.hasClass(dsel, 'collapsed')){
                YAHOO.util.Dom.replaceClass(dsel, 'collapsed', 'expanded');
                var assessments=$GT('sid~'+setid+'~assessments');
                if (assessments){
                    assessments.style.display='';
                } else {
                    var assessments=createEl('ul', {id:'sid~'+setid+'~assessments', 'class':'assessmentlist'});
                    dsel.parentNode.appendChild(assessments); // apply to parent (list)
                    assessments.innerHTML='<li id="sid~'+setid+'~assessments~ldr" class="ajaxloading">Please wait, loading assessment list</li>';
                    // Request assessments
                    me.REQ_assessments(setid);
                }
            } else {          
                // else expanded so collapse
                YAHOO.util.Dom.replaceClass(dsel, 'expanded', 'collapsed');
                var assessments=$GT('sid~'+setid+'~assessments');
                if (assessments){
                    assessments.style.display='none';
                }
            }
        },
        
        //
        // Request assessments
        //
        REQ_assessments:function(setid){
            var reqURL=mis_ajax_url+'ajax_getassessments.php';
            sendstr="setid="+setid;
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_assessments(o, setid)}, failure:function(){alert('failed '+reqURL)}});             
        },
        
        //
        // Received assessments
        //
        REC_assessments:function(o, setid){
            var baseid='sid~'+setid+'~assessments';
            // remove loader text            
            var ldr=$GT(baseid+'~ldr');
            ldr.parentNode.removeChild(ldr);
            // Check for errors and Convert response to DomXML object            
            var respVal=AJAXResponseToDomXMLValidate(o, 'assessments', true);
            if (respVal.error){
                return; // abort on any errors
            }
            
            var assessments=$GT(baseid);
            
            var resp=respVal.resp;
            var assitems=resp.getElementsByTagName('assessment');
            for (var a=0; a<assitems.length; a++){
                var assitem=assitems[a];
                var assid=XMLFirstTagValUnpack(assitem,'id');
                var assname=XMLFirstTagValUnpack(assitem,'name');              
                var asselid='sid~'+setid+'~assessment~'+assid; // assessment element id
                var li=createEl('li', {id:asselid, 'class':'assessment'});                
                var anc=unlinkedAnchor({id:asselid+'~click', 'class':'collapsed'});
                
                /*
                var html='<span>'+assname+'</span>';
                html+='<label>display</label> <input type="checkbox" id="'+baseid+assid+'~'+'checkbox" name="'+baseid+assid+'~'+'checkbox" />';
                */
                
                anc.innerHTML=assname;
                li.appendChild(anc);
                
                assessments.appendChild(li);
                me.applyClick_assessment($GT(asselid+'~click')); // apply click event
            }
        },
        
        
        //
        // Apply click event for specific assessment element
        //
        applyClick_assessment:function(assel){
            YAHOO.util.Event.addListener(assel, 'click', function(){me.toggleAssessment(assel)});
        },

        //
        // Open / Close assessment to show / hide exams and criteria
        //
        toggleAssessment:function(assel){            
            var tmparr=assel.id.split('~');
            var setid=tmparr[1];
            var assid=tmparr[4];                                 
            var baseid='sid~'+setid+'~assessment~'+assid;
             // if collapsed then expanded
            if (YAHOO.util.Dom.hasClass(assel, 'collapsed')){
                YAHOO.util.Dom.replaceClass(assel, 'collapsed', 'expanded');
                var assbranch=$GT(assel.id+'~branch');
                if (assbranch){
                    assbranch.style.display='';
                } else {
                    var assbranch=createEl('ul', {id:baseid+'~branch', 'class':'assbranch'});
                    assel.parentNode.appendChild(assbranch); // apply to parent (list)
                    var branchhtml='<li id="'+baseid+'~criteria"><a class="criteria collapsed" href="#" onclick="return(false);" id="'+baseid+'~criteria~click">Criteria</a></li>';
                    branchhtml+='<li id="'+baseid+'~exams"><a class="exams collapsed" href="#" onclick="return(false);" id="'+baseid+'~exams~click">Exams</a></li>';
                    assbranch.innerHTML=branchhtml;
                    me.applyClick_criteria($GT(baseid+'~criteria~click')); // apply criteria click event
                    me.applyClick_exams($GT(baseid+'~exams~click')); // apply exams click event
                }
            } else {            
                // else expanded so collapse
                YAHOO.util.Dom.replaceClass(assel, 'expanded', 'collapsed');
                var assbranch=$GT(baseid+'~branch');
                if (assbranch){
                    assbranch.style.display='none';
                }
            }
        },
        
        applyClick_criteria:function(el){
            YAHOO.util.Event.addListener(el, 'click', function(){me.toggleCriteria(el)});
        },
        
        applyClick_exams:function(el){
            YAHOO.util.Event.addListener(el, 'click', function(){me.toggleExams(el)});
        },

        toggleCriteria:function(el){
            var tmparr=el.id.split('~');
            var setid=tmparr[1];
            var assid=tmparr[3]; 
            var baseid='sid~'+setid+'~assessment~'+assid;
            var critbranch=$GT(baseid+'~critbranch');
            // if collapsed then expand
            if (YAHOO.util.Dom.hasClass(el, 'collapsed')){
                YAHOO.util.Dom.replaceClass(el, 'collapsed', 'expanded');                
                if (critbranch){
                    // reveal critbranch
                    critbranch.style.display='';
                } else {
                    // create critbranch
                    var critbranch=createEl('ul', {id:baseid+'~critbranch', 'class':'critbranch'});
                    el.parentNode.appendChild(critbranch); // apply to parent (list)
                    critbranch.innerHTML='<li id='+baseid+'~ldr" class="ajaxloading">Please wait, loading...</li>';
                }
                me.REQ_assessment_criteria(setid, assid, critbranch);
            } else {            
                // else expanded so collapse
                YAHOO.util.Dom.replaceClass(el, 'expanded', 'collapsed');
                // hide critbranch
                critbranch.style.display='none';
            }        
        },
        
        //
        // Request assessment criteria
        //
        REQ_assessment_criteria:function(setid, assid, critbranch){
            var reqURL=mis_ajax_url+'ajax_getassessmentcriteria.php';
            sendstr="setid="+setid+"&assid="+assid;
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_assessment_criteria(o, setid, assid, critbranch)}, failure:function(){alert('failed '+reqURL)}});             
        },

        //
        // Received assessment criteria        
        //
        REC_assessment_criteria:function(o, setid, assid, critbranch){        
            critbranch.innerHTML='';
            var baseid='sid~'+setid+'~assessment~'+assid+'~criteria~';        
            // Check for errors and Convert response to DomXML object            
            var respVal=AJAXResponseToDomXMLValidate(o, 'assessmentcriteria', true);
            if (respVal.error){
                return; // abort on any errors
            }
            var resp=respVal.resp;
            var criteria=resp.getElementsByTagName('crit');
            for (c=0; c<criteria.length; c++){
                var crit=criteria[c];
                var map=XMLFirstTagValUnpack(crit, 'map');
                var label=XMLFirstTagValUnpack(crit, 'label');
                var display=XMLFirstTagValUnpack(crit, 'display');
                var checked=display==1 ? ' checked="checked" ' : '';
                var labelcustom=XMLFirstTagValUnpack(crit, 'labelcustom');
                var li=createEl('li', {id:baseid+map, 'class':'criteria'});
                var html='<label class="criteria_label">'+label+'</label><input class="criteria_input" id="'+baseid+map+'~'+'input" name="'+baseid+map+'~'+'input" type="text" value="'+labelcustom+'" />';
                html+='<label>display</label> <input class="criteria_checkbox" type="checkbox" id="'+baseid+map+'~'+'checkbox" name="'+baseid+map+'~'+'checkbox"'+checked+' />';
                li.innerHTML=html;
                critbranch.appendChild(li);
            }            
        },

        toggleExams:function(el){
            var tmparr=el.id.split('~');
            var setid=tmparr[1];
            var assid=tmparr[3]; 
            var baseid='sid~'+setid+'~assessment~'+assid;
            var examsbranch=$GT(baseid+'~examsbranch');
            // if collapsed then expanded
            if (YAHOO.util.Dom.hasClass(el, 'collapsed')){
                YAHOO.util.Dom.replaceClass(el, 'collapsed', 'expanded');                
                if (examsbranch){
                    // reveal examsbranch
                    examsbranch.style.display='';
                } else {
                    // create examsbranch
                    var examsbranch=createEl('ul', {id:baseid+'~examsbranch', 'class':'examsbranch'});
                    el.parentNode.appendChild(examsbranch); // apply to parent (list)
                    examsbranch.innerHTML='<li id='+baseid+'~ldr" class="ajaxloading">Please wait, loading...</li>';                    
                    // Request list of exams
                    me.REQ_exams(setid, assid, examsbranch);
                }                
            } else {            
                // else expanded so collapse
                YAHOO.util.Dom.replaceClass(el, 'expanded', 'collapsed');
                // hide examsbranch
                examsbranch.style.display='none';
            }
        },
        
        
        //
        // Request exams
        //
        REQ_exams:function(setid, assid, examsbranch){
            var reqURL=mis_ajax_url+'ajax_getexams.php';
            sendstr="setid="+setid+"&assid="+assid;
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_exams(o, setid, assid, examsbranch)}, failure:function(){alert('failed '+reqURL)}});             
        },        
        
        REC_exams:function(o, setid, assid, examsbranch){
            examsbranch.innerHTML='';
            var baseid='sid~'+setid+'~assessment~'+assid+'~exam~';        
            // Check for errors and Convert response to DomXML object            
            var respVal=AJAXResponseToDomXMLValidate(o, 'exams', true);
            if (respVal.error){
                return; // abort on any errors
            }
            var resp=respVal.resp;
            var exams=resp.getElementsByTagName('exam');
            for (var e=0; e<exams.length; e++){
                var exam=exams[e];
                var eid=XMLFirstTagValUnpack(exam, 'id');
                var ename=XMLFirstTagValUnpack(exam, 'name');
                var enamecustom=XMLFirstTagValUnpack(exam, 'namecustom');
                var li=createEl('li', {id:baseid+eid, 'class':'exam'});
                var a=unlinkedAnchor({id:baseid+eid+'~click', 'class':'examcode collapsed'});                
                var s=createEl('span');
                s.innerHTML='<span style="display:none" class="exam_name" id="'+baseid+eid+'~name">'+enamecustom+'</span><input class="exam_namecustom" type="text" name="'+baseid+eid+'~input" id="'+baseid+eid+'~input" value="'+enamecustom+'" />';
                a.href='#';
                a.innerHTML=eid;
                li.appendChild(a);
                li.appendChild(s);
                examsbranch.appendChild(li);
                me.applyClick_exam(a);
            }
        },
        
        applyClick_exam:function(el){            
            YAHOO.util.Event.addListener(el, 'click', function(){me.toggleExam(el)});
        },

        toggleExam:function(el){            
            var tmparr=el.id.split('~');
            var setid=tmparr[1];
            var assid=tmparr[3];  
            var examid=tmparr[5];
            var baseid='sid~'+setid+'~assessment~'+assid+'~exam~'+examid+'~';
            var exambranch=$GT(baseid+'exambranch');
            // if collapsed then expand          
            if (YAHOO.util.Dom.hasClass(el, 'collapsed')){
                YAHOO.util.Dom.replaceClass(el, 'collapsed', 'expanded');
                if (exambranch){                
                    // reveal exambranch
                    exambranch.style.display='';
                } else {
                    // create exambranch
                    var exambranch=createEl('ul', {id:baseid+'exambranch', 'class':'exambranch'});
                    el.parentNode.appendChild(exambranch); // apply to parent (list)
                    exambranch.innerHTML='<li id='+baseid+'~ldr" class="ajaxloading">Please wait, loading...</li>';                    
                    me.REQ_exam_years(setid, assid, examid, exambranch);
                }                
            } else {            
                // else expanded so collapse
                YAHOO.util.Dom.replaceClass(el, 'expanded', 'collapsed');
                // hide exambranch
                exambranch.style.display='none';                
            }
            
        },
        REQ_exam_years:function(setid, assid, examid, exambranch){
            var reqURL=mis_ajax_url+'ajax_getexamyears.php';
            sendstr="setid="+setid+"&assid="+assid+"&examid="+examid;
            AJAXPost(reqURL, sendstr, {success:function(o){me.REC_exam_years(o, setid, assid, examid, exambranch)}, failure:function(){alert('failed '+reqURL)}});            
        },
        REC_exam_years:function(o, setid, assid, examid, exambranch){
            exambranch.innerHTML='';
            var baseid='sid~'+setid+'~assessment~'+assid+'~exam~'+examid+'~year~';        
            // Check for errors and Convert response to DomXML object            
            var respVal=AJAXResponseToDomXMLValidate(o, 'examyears', true);
            if (respVal.error){
                return; // abort on any errors
            }
            var resp=respVal.resp;
            var years=resp.getElementsByTagName('examyear');
            for (var y=0; y<years.length; y++){
                var year=years[y];
                var yid=XMLFirstTagValUnpack(year, 'id');               
                var yname=XMLFirstTagValUnpack(year, 'year');
                var ydisplay=XMLFirstTagValUnpack(year, 'display');
                var checked=ydisplay==1 ? ' checked="checked" ' : '';
                var yfrom=XMLFirstTagValUnpack(year, 'displayfrom');
                var yto=XMLFirstTagValUnpack(year, 'displayto');
                var li=createEl('li', {id:baseid+yid,'class':'examyear'});
                //li.innerHTML='<div style="min-width:100px; float:left"><label class="examyear_label">'+yname+' </label>'+'<label>include</label><input type="checkbox" class="examyear_checkbox" name="'+baseid+yid+'~checkbox" id="'+baseid+yid+'~checkbox" '+checked+'/></div><div style="float:left"><label>from</label><input type="text" value="'+yfrom+'" style="width:80px" class="w8em format-d-m-y divider-dash highlight-days-67 no-transparency examyear_from" /><label>until</label><input type="text" value="'+yto+'" style="width:80px" class="w8em format-d-m-y divider-dash highlight-days-67 no-transparency examyear_to" /></div><div class="clearer"></div>';
                
                var html='<div style="min-width:100px; float:left"><label class="examyear_label">'+yname+' </label>'+'<label>include</label><input type="checkbox" class="examyear_checkbox" name="'+baseid+yid+'~checkbox" id="'+baseid+yid+'~checkbox" '+checked+' /></div>';
                html+='<div style="float:left"><label>from</label><input type="text" value="'+yfrom+'" style="width:80px" class="w8em format-d-m-y divider-dash highlight-days-67 no-transparency examyear_from" /><label>until</label><input type="text" value="'+yto+'" style="width:80px" class="w8em format-d-m-y divider-dash highlight-days-67 no-transparency examyear_to" /></div><div style="clear:both"></div>';
                li.innerHTML=html;
                
                exambranch.appendChild(li);
            }
            datePickerController.create();
        },
        save:function(){            
            var conftree=$GT('manageassessments');
            
            // Get config for assessment data sets
            var ds_els=YAHOO.util.Dom.getElementsByClassName('setid', 'li', conftree);
            var setids=[];
            for (var d=0; d<ds_els.length; d++){
                var ds_el=ds_els[d];
                var tmparr=ds_el.id.split('~');
                var setid=tmparr[1]
                
                // get setid checkbox display value
                var ds_el_cbxs=YAHOO.util.Dom.getElementsByClassName('setid_checkbox', 'input', ds_el);
                var ds_el_cbx=ds_el_cbxs[0];
                
                // get assessment config
                var assessconf=me.getassessconf(ds_el);
                
                // add setid and assessment conf to setids array
                setids.push({id:setid, checked:ds_el_cbx.checked, assessconf:assessconf});
            }

            var json=YAHOO.lang.JSON.stringify(setids);
            
            // update form json
            $GT('assessmentjson').value=json;
            
            // submit form
            document.forms['assessments'].submit();
            
            /*
            var json='{setids:[';
            for (var s=0; s<setids.length; s++){
                var set=setids[s];
                json+=s>0 ? ',' : '';
                json+='{id:"'+set.id+'", checked:'+set.checked+', assessconf:[';
                for (var a=0; a<assessconf.length; a++){
                    var ass=assessconf[a];
                    json+='{criteria
                }
                json+='}';                
            }
            json='}';
            */


        },
        //
        // ds_el - data set element
        //
        getassessconf:function(ds_el){
            // config for assessments
            var assess_els=YAHOO.util.Dom.getElementsByClassName('assessment', 'li', ds_el); 
            var assessments=[];
            for (var a=0; a<assess_els.length; a++){            
                var assess_el=assess_els[a];
                var tmparr=assess_el.id.split('~');
                var assessid=tmparr[4];
                
                // get criteria
                var crit_els=YAHOO.util.Dom.getElementsByClassName('criteria', 'li', assess_el);
                var criteria=[];
                for (var c=0; c<crit_els.length; c++){
                    var crit_el=crit_els[c];
                    var tmparr=crit_el.id.split('~');
                    var mapval=tmparr[5];
                    var lab_els=YAHOO.util.Dom.getElementsByClassName('criteria_label', 'label', crit_el);
                    var lab_el=lab_els[0];
                    var label=lab_el.innerHTML;
                    var labinp_els=YAHOO.util.Dom.getElementsByClassName('criteria_input', 'input', crit_el);
                    var labinp_el=labinp_els[0];
                    var labelcustom=labinp_el.value; // custom label to be displayed
                    var labcb_els=YAHOO.util.Dom.getElementsByClassName('criteria_checkbox', 'input', crit_el);
                    var labcb_el=labcb_els[0];
                    var display=labcb_el.checked;
                    criteria.push({mapval:mapval, label:label, labelcustom:labelcustom, display:display});
                }
                                
                // get exams
                var exam_els=YAHOO.util.Dom.getElementsByClassName('exam', 'li', assess_el);
                var exams=[];
                for (var e=0; e<exam_els.length; e++){
                    var exam_el=exam_els[e];
                    var exam_ancs=YAHOO.util.Dom.getElementsByClassName('examcode', 'a', exam_el);
                    var exam_anc=exam_ancs[0];
                    var code=exam_anc.innerHTML;                   
                    var examinp_els=YAHOO.util.Dom.getElementsByClassName('exam_namecustom', 'input', exam_el);
                    var examinp_el=examinp_els[0];
                    var namecustom=examinp_el.value;
                    var examname_els=YAHOO.util.Dom.getElementsByClassName('exam_name', 'span', exam_el);
                    var examname_el=examname_els[0];
                    var name=examname_el.innerHTML;
                    var examyearconf=me.getexamyearconf(exam_el);
                    exams.push ({examid:code, name:name, namecustom:namecustom, examyearconf:examyearconf});
                }
                if (typeof(criteria[0])!='undefined' || typeof(exams[0])!='undefined'){
                    assessments.push({assessid:assessid, criteria:criteria, exams:exams});
                }
            } 
            return (assessments);
        },
        
        getexamyearconf:function(exam_el){
            var year_els=YAHOO.util.Dom.getElementsByClassName('examyear', 'li', exam_el);
            var years=[];
            for (var y=0; y<year_els.length; y++){
                var year=year_els[y];
                var year_labels=YAHOO.util.Dom.getElementsByClassName('examyear_label', 'label', year);
                var yearlab=year_labels[0];
                var yearval=yearlab.innerHTML;
                var year_cbs=YAHOO.util.Dom.getElementsByClassName('examyear_checkbox', 'input', year);
                var year_cb=year_cbs[0];
                var year_display=year_cb.checked;
                var year_inpfroms=YAHOO.util.Dom.getElementsByClassName('examyear_from', 'input', year);
                var year_inpfrom=year_inpfroms[0];
                var year_from=year_inpfrom.value;
                var year_inptos=YAHOO.util.Dom.getElementsByClassName('examyear_to', 'input', year);
                var year_inpto=year_inptos[0];
                var year_to=year_inpto.value;
                years.push({year:yearval, display:year_display, displayfrom:year_from, displayto:year_to});
            }
            return (years);
        }
        
    };
    me.constructor();
    return (me);     
}

var manageassessments_inst=new manageassessments();
});