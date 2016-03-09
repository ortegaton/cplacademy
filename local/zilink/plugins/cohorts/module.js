M.local_zilink_cohorts_view = {
        
        init: function(Y, sesskey, domain) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.domain = domain;
            this.xhr = null;
            this.response = null;
            
            Y.one('#zilink_cohorts_view_serach_term').on('keyup', function(e) {
                
                var searchstring = e.target.get('value');
            
                var Y = this.Y;
    
                uri = this.domain+'/local/zilink/plugins/cohorts/action.php';
                if (this.xhr != null) {
                    this.xhr.abort();
                }
                
                this.xhr = Y.io(uri, {
                    
                    data: '&filter='+searchstring
                        +'&type=cohort'
                        +'&sesskey='+this.sesskey,
                    context: this,
                    on: {
                        success: function(id, o) {
                            
                            var cohortlist = document.getElementById('menuzilink_cohorts_view_cohort_list');
                            
                            for(i=cohortlist.length-1;i>=0;i--)
                            {
                                cohortlist.remove(i);
                            }
                            
                            
                            var response = Y.JSON.parse(o.responseText);
                            
                            if(response.length == 0)
                            {
                                elOptNew = document.createElement('option');
                                elOptNew.text = 'No Cohorts Found';
                                elOptNew.value = 0;
                                
                                try {
                                        cohortlist.add(elOptNew, null);
                                    }
                                    catch(ex) {
                                        cohortlist.add(elOptNew);
                                    } 
                            }
                            else {
                                for (s in response) {
                                    
                                    elOptNew = document.createElement('option');
                                    elOptNew.text = response[s].name;
                                    elOptNew.value = response[s].id;            
                                                    
                                    try {
                                        cohortlist.add(elOptNew, null);
                                    }
                                    catch(ex) {
                                        cohortlist.add(elOptNew);
                                    }
                                }
                            }
    
                            //instance.progress.setStyle('visibility', 'hidden');
                        },
                        failure: function(id, o) {
                            if (o.statusText != 'abort') {
                                //instance.progress.setStyle('visibility', 'hidden');
                                //if (o.statusText !== undefined) {
                                //    instance.listcontainer.set('innerHTML', o.statusText);
                                //}
                            }
                        }
                    }
                });
            },this),
            
            Y.one('#menuzilink_cohorts_view_cohort_list').on('click', function(e) {
            
                var searchstring = e.target.get('value');
                
                var Y = this.Y;
    
                uri = this.domain+'/local/zilink/plugins/cohorts/action.php';
                if (this.xhr != null) {
                    this.xhr.abort();
                }
                //instance.progress.setStyle('visibility', 'visible');
                this.xhr = Y.io(uri, {
    
                    data: '&filter='+searchstring
                        +'&type=members'
                        +'&sesskey='+this.sesskey,
                    context: this,
                    on: {
                        success: function(id, o) {
                            var response = Y.JSON.parse(o.responseText);
    
                            var studentlist = document.getElementById('menuzilink_cohorts_view_student_list');
                                
                            for(i=studentlist.length-1;i>=0;i--)
                            {
                                studentlist.remove(i);
                            }
                            
                            var response = Y.JSON.parse(o.responseText);
                            
                            if(response.length == 0)
                            {
                                elOptNew = document.createElement('option');
                                elOptNew.text = 'No Cohort Members Found';
                                elOptNew.value = 0;
                                
                                try {
                                        studentlist.add(elOptNew, null);
                                    }
                                    catch(ex) {
                                        studentlist.add(elOptNew);
                                    } 
                            }
                            else {
                                for (s in response) {
                                    
                                    elOptNew = document.createElement('option');
                                    elOptNew.text = response[s].firstname + ' '+ response[s].lastname;
                                    elOptNew.value = response[s].idnumber;          
                            
                                    try {
                                        studentlist.add(elOptNew, null);
                                    }
                                    catch(ex) {
                                        studentlist.add(elOptNew);
                                    } 
        
                                }
                               }
                            //instance.progress.setStyle('visibility', 'hidden');
                        },
                        failure: function(id, o) {
                            if (o.statusText != 'abort') {
                                //instance.progress.setStyle('visibility', 'hidden');
                                //if (o.statusText !== undefined) {
                                //    instance.listcontainer.set('innerHTML', o.statusText);
                                //}
                            }
                        }
                    }
                });
            },this);
    }
}    