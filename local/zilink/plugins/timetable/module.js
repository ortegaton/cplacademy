M.local_zilink_timetable = {
        
        init: function(Y, domain, idnumber, sesskey) {
            this.Y = Y;
            this.domain = domain;
            this.idnumber = idnumber;
            this.sesskey = sesskey;
            this.xhr = null;
            this.offset = 0;

            Y.one('#zilinkweekselector').on('change', function(e) {
                e.preventDefault();
                
                var sel;
                var opt;
                
                sel = document.getElementById('zilinkweekselector');
                opt = sel.options[sel.selectedIndex];
                this.offset = opt.value; 
                this.updateview();
            }, this);
            
        },
        
        updateview: function() {
             
            var Y = this.Y;
            uri = this.domain+'/local/zilink/plugins/timetable/action.php';
            if (this.xhr != null) {
                this.xhr.abort();
            }

            Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'visible');
            Y.one('#zilink_timetableupdateprogress').setStyle('width', '10px');
            
            Y.one('#zilink_timetableupdatefailed').setStyle('visibility', 'hidden');
            Y.one('#zilink_timetableupdatesuccess').setStyle('visibility', 'hidden');
            
            this.xhr = Y.io(uri, {
                data: 'action=ttviewupdate&offset='+this.offset+'&sesskey='+this.sesskey +'&idnumber='+this.idnumber,
                context: this,
                on: {
                    success: function(id, o) {
                       results = Y.Node.create(o.responseText);
                       Y.one('#zilinktimetable').replace(results);
                       results.setAttribute('id', 'zilinktimetable');
                       
                       Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'hidden');
                       Y.one('#zilink_timetableupdateprogress').setStyle('width', '0px');
                       
                       Y.one('#zilink_timetableupdatesuccess').setStyle('visibility', 'visible');
                       Y.one('#zilink_timetableupdatesuccess').setStyle('width', '10px');
                    },
                    failure: function(id, o) {
                         Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'hidden');
                         Y.one('#zilink_timetableupdatefailed').setStyle('visibility', 'visible');
                         Y.one('#zilink_timetableupdatesuccess').setStyle('width', '10px'); 
                    },
                    end: function (id, o) {
                        Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'hidden');
                        Y.one('#zilink_timetableupdateprogress').setStyle('width', '0px');
                        
                    }
                }
            });
        }
}