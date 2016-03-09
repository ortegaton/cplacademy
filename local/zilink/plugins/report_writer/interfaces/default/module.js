M.local_zilink_report_writer = {
        
        init: function(Y, domain,courseid, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.domain = domain;
            this.reportid  = 0;
            this.courseid = courseid;
            
            //Y.one('#zilink_report_update_progress').setStyle('visibility', 'visible');
            //Y.one('#zilink_report_update_progress').setStyle('width', '10px');
            
            //Y.one('#zilink_report_update_failed').setStyle('visibility', 'hidden');
            //Y.one('#zilink_report_update_success').setStyle('visibility', 'hidden');
            
            Y.all('#zilink_view_reports').on('click', function(e) {
                e.preventDefault();
                
                this.reportid = e.currentTarget.getAttribute("name").toString();
                this.updatepupilsfromgroup();
            }, this);
            
        },
        
        updatepupilsfromgroup: function() {
            
            var Y = this.Y;
            uri = this.domain+'/local/zilink/plugins/report_writer/interfaces/default/pages/action.php';
            if(this.xhr != null) {
                this.xhr.abort();
            }
            this.xhr = Y.io(uri, {
                data: 'action=view_pupil_reports&cid='+this.courseid +'&rid='+ this.reportid +'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {      
                       results = Y.Node.create(o.responseText);
                       Y.one('#pupillist').replace(results);
                       results.setAttribute('id', 'pupillist');
                       
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