M.local_zilink_guardian_scheduler = {

    init: function(Y, sessionid, guardianid, studentid, sesskey) {
        this.Y = Y;
        this.sessionid = sessionid;
        this.guardianid = guardianid;
        this.studentid = studentid
        this.sesskey = sesskey;
        Y.all('select').on('change', function(e) {
                
                var sel;
                var opt;
                var teacher = e.currentTarget.getAttribute("name").toString();
                
                if(teacher.indexOf("teacher") > -1) 
                {
                    e.preventDefault();
                    var subject = teacher.substr(teacher.indexOf("[")+1,(teacher.indexOf("]")-1)-teacher.indexOf("["));
                
                    sel = document.getElementById(e.currentTarget.getAttribute("id").toString());
                    opt = sel.options[sel.selectedIndex];
                
                    this.refresh_schedule(subject,opt.value);
                }
            }, this);
    },

    refresh_schedule: function(subjectid, teacherid) {
        Y = this.Y;

        Y.io(M.cfg.wwwroot+'/local/zilink/plugins/guardian/scheduler/pages/action.php', {
            method: 'get',
            data: 'session='+this.sessionid+'&teacher='+teacherid+'&student='+this.studentid+'&guardian='+this.guardianid+'&subject='+this.subjectid,
            context: this,
            on: {
                success: function(id, o) {
                    response = Y.JSON.parse(o.responseText);
                    
                    var times = document.getElementById(Y.one('*[name="times['+subjectid+']"]').getAttribute("id").toString())
                    //var times = Y.one('*[name="times['+subjectid+']"]');
                    
                    for (i = times.length - 1; i >= 0; i--) {
                        times.remove(i);
                    }
                    
                    var elOptNew;
                    
                    for (g in response.slots) {
                        
                        elOptNew = document.createElement('option');
                        elOptNew.text = response.slots[g].displaytime;
                        elOptNew.value = response.slots[g].time;

                        try {
                            times.add(elOptNew, null);
                        } catch(ex) {
                            times.add(elOptNew);
                        }
                    }
                },
                failure: function(id, o) {
                    alert(M.util.get_string('formfailed', 'local_zilink')+' '+this.altmethod);
                }
            }
        });
    }
},
M.local_zilink_guardian_scheduler_onbehalf = {

    init: function(Y, sesskey) {
        this.Y = Y;
        this.sesskey = sesskey;
        Y.all('select').on('change', function(e) {
                
                var sel;
                var opt;
                
                var select = e.currentTarget.getAttribute("name").toString();
                
                if(select.indexOf("subjects") > -1) 
                {
                    e.preventDefault();
                    sel = document.getElementById(e.currentTarget.getAttribute("id").toString());
                    opt = sel.options[sel.selectedIndex];
                    
                    var time = select.substr(select.indexOf("[")+1,(select.indexOf("]")-1)-select.indexOf("["));

                    this.refresh_students(time,opt.value);
                } else if (select.indexOf("students") > -1) {
                    e.preventDefault();
                    sel = document.getElementById(e.currentTarget.getAttribute("id").toString());
                    opt = sel.options[sel.selectedIndex];
                
                    var time = select.substr(select.indexOf("[")+1,(select.indexOf("]")-1)-select.indexOf("["));
                    this.refresh_guardians(time,opt.value);
                } 
            }, this);
    },

    refresh_students: function(time, subjectid) {
        Y = this.Y;

        Y.io(M.cfg.wwwroot+'/local/zilink/plugins/guardian/scheduler/pages/action.php', {
            method: 'get',
            data: 'action=students&subject='+subjectid+'&sesskey='+this.sesskey,
            context: this,
            on: {
                success: function(id, o) {
                    response = Y.JSON.parse(o.responseText);
                    console.log(response);
                    var students = document.getElementById(Y.one('*[name="students['+time+']"]').getAttribute("id").toString())

                    
                    for (i = students.length - 1; i >= 0; i--) {
                        students.remove(i);
                    }
                    
                    var elOptNew;
                    
                    for (g in response) {
                        
                        elOptNew = document.createElement('option');
                        elOptNew.text = response[g].name;
                        elOptNew.value = response[g].id;

                        try {
                            students.add(elOptNew, null);
                        } catch(ex) {
                            students.add(elOptNew);
                        }
                    }
                },
                failure: function(id, o) {
                    alert(M.util.get_string('formfailed', 'local_zilink')+' '+this.altmethod);
                }
            }
        });
    },
    refresh_guardians: function(time, studentid) {
        Y = this.Y;

        Y.io(M.cfg.wwwroot+'/local/zilink/plugins/guardian/scheduler/pages/action.php', {
            method: 'get',
            data: 'action=guardians&student='+studentid+'&sesskey='+this.sesskey,
            context: this,
            on: {
                success: function(id, o) {
                    response = Y.JSON.parse(o.responseText);
                    
                    var guardians = document.getElementById(Y.one('*[name="guardians['+time+']"]').getAttribute("id").toString())

                    
                    for (i = guardians.length - 1; i >= 0; i--) {
                        guardians.remove(i);
                    }
                    
                    var elOptNew;
                    
                    for (g in response) {
                        
                        elOptNew = document.createElement('option');
                        elOptNew.text = response[g].name;
                        elOptNew.value = response[g].id;

                        try {
                            guardians.add(elOptNew, null);
                        } catch(ex) {
                            guardians.add(elOptNew);
                        }
                    }
                },
                failure: function(id, o) {
                    alert(M.util.get_string('formfailed', 'local_zilink')+' '+this.altmethod);
                }
            }
        });
    }
}