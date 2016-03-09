M.local_zilink_homework = {
        
        init: function(Y, domain,courseid, userid, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.domain = domain;
            this.userid = userid;
            this.cohortid = 0;
            this.start  = 0;
            this.end = 0;
            this.courseid = courseid;
            
            //Y.one('#zilink_report_update_progress').setStyle('visibility', 'visible');
            //Y.one('#zilink_report_update_progress').setStyle('width', '10px');
            
            //Y.one('#zilink_report_update_failed').setStyle('visibility', 'hidden');
            //Y.one('#zilink_report_update_success').setStyle('visibility', 'hidden');
            
            Y.all('#zilink_view_homework').on('click', function(e) {
                e.preventDefault();
                
                var items = e.currentTarget.getAttribute("name").toString().split("-");
                this.cohortid = items[0];
                this.start = items[1];
                this.end = items[2];
                this.updatehomeworklist();
            }, this);
            
        },
        
        updatehomeworklist: function() {
            
            var Y = this.Y;
            uri = this.domain+'/local/zilink/plugins/homework/report/interfaces/default/pages/action.php';
            if(this.xhr != null) {
                this.xhr.abort();
            }
            //console.log('action=view_homeworks&cid='+this.courseid +'&cohortid='+ this.cohortid + '&start='+ this.start + '&end='+ this.end +'&sesskey='+this.sesskey);
            
            this.xhr = Y.io(uri, {
                data: 'action=view_homeworks&cid='+ this.cohortid + '&uid=' + this.userid+ '&start='+ this.start + '&end='+ this.end +'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {      
                       results = Y.Node.create(o.responseText);
                       Y.one('#homeworklist').replace(results);
                       results.setAttribute('id', 'homeworklist');
                       
                       //Y.one('#zilink_report_update_progress').setStyle('visibility', 'hidden');
                       //Y.one('#zilink_report_update_progress').setStyle('width', '0px');
                           
                       //Y.one('#zilink_report_update_success').setStyle('visibility', 'visible');
                       //Y.one('#zilink_report_update_success').setStyle('width', '10px');
                    },
                    failure: function(id, o) {
                        //Y.one('#zilink_report_update_progress').setStyle('visibility', 'hidden');
                        //Y.one('#zilink_report_update_failed').setStyle('visibility', 'visible');
                        //Y.one('#zilink_report_update_failed').setStyle('width', '10px'); 
                    },
                    end: function (id, o) {
                        //Y.one('#zilink_report_update_progress').setStyle('visibility', 'hidden');
                        //Y.one('#zilink_report_update_progress').setStyle('width', '0px');
                    }
                }
            });
        }
}