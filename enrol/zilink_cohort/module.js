M.enrol_zilink_cohort_list = {
        
        init: function(Y, sesskey, domain, courseid) {
            this.Y = Y;

            this.sesskey = sesskey;
            this.courseid = courseid;
            this.domain = domain;
            this.xhr = null;
            this.response = null;
            
            Y.one('#id_cohort_filter').on('keyup', function(e) {
                
                var searchstring = e.target.get('value');
            
                var Y = this.Y;
    
                uri = this.domain+'/enrol/zilink_cohort/action.php';
                if (this.xhr != null) {
                    this.xhr.abort();
                }
                
                this.xhr = Y.io(uri, {
                    
                    data: '&filter='+searchstring
                        +'&type=cohort'
                        +'&sesskey='+this.sesskey
                        +'&courseid='+this.courseid,
                    context: this,
                    on: {
                        success: function(id, o) {
                            
                            var cohortlist = document.getElementById('id_cohortids');
                            
                            for(i=cohortlist.length-1;i>=0;i--)
                            {
                                cohortlist.remove(i);
                            }
                            
                            
                            var response = Y.JSON.parse(o.responseText);
                            console.log(response);
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
            },this);
    }
}    