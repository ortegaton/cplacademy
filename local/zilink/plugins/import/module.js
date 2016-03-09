M.local_zilink_import = {
        
    init: function(Y, domain, sesskey) {
        this.Y = Y;
        this.domain = domain;
        this.sesskey = sesskey;
        this.xhr = null;
        
        setInterval(this.updatelog, 10000, Y ,domain,sesskey);
        
    },
    
    updatelog: function(Y, domain, sesskey) {
        
        console.log("Requesting Log");
        
        uri = domain+'/local/zilink/plugins/import/action.php';
        if (this.xhr != null) {
            this.xhr.abort();
        }
        
        this.xhr = Y.io(uri, {
            data: 'action=updatelog&sesskey='+sesskey,
            context: this,
            on: {
                success: function(id, o) {
                   results = Y.Node.create(o.responseText);
                   Y.one('#consolelog').replace(results);
                   results.setAttribute('id', 'consolelog');
                   console.log("Log Updated");
                   
                },
                failure: function(id, o) {
                      
                },
                end: function (id, o) {
                    
                }
            }
        });
    }
}