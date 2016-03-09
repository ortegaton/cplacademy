M.local_zilink_bookings_room_dates = {
    
        init: function(Y, domain,day, period, sesskey) {
            this.Y = Y;
            this.domain = domain;
            this.day = day;
            this.period = period;
            this.sesskey = sesskey;
            this.xhr = null;
            
            Y.one('#zilink_bookings_rooms_booked_room').on('change', function(e) {
                e.preventDefault();
                
                var sel;
                var opt;
                                
                sel = document.getElementById('zilink_bookings_rooms_booked_room');
                opt = sel.options[sel.selectedIndex];
                this.room = opt.value; 
                this.updatedates();
            }, this);
            
        },
        
        updatedates: function() {
            
            var Y = this.Y;
            uri = this.domain+'/local/zilink/plugins/bookings/rooms/action.php';
            if (this.xhr != null) {
                this.xhr.abort();
            }
            
            Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'visible');
            Y.one('#zilink_timetableupdateprogress').setStyle('width', '10px');
            
            Y.one('#zilink_timetableupdatefailed').setStyle('visibility', 'hidden');
            Y.one('#zilink_timetableupdatesuccess').setStyle('visibility', 'hidden');

            this.xhr = Y.io(uri, {
                data: 'action=bookings_rooms_available_dates_update&day='+this.day+'&period='+this.period+'&room='+this.room+'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {
                           results = Y.Node.create(o.responseText);
                           Y.one('#zilink_bookings_rooms_available_dates').replace(results);
                           results.setAttribute('id', 'zilink_bookings_rooms_available_dates');
                           
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
},

M.local_zilink_bookings_room_cancel_booking = {
    
        init: function(Y, domain, sesskey) {
            this.Y = Y;
            this.domain = domain;
            this.sesskey = sesskey;
            this.id = 0;
            this.xhr = null;
            
            Y.one('#zilink_bookings_rooms_current_bookings').on('click', function(e) {
                e.preventDefault();
                
                this.id = e._event.target.getAttribute("name").toString();
                this.cancelbooking();
                
            }, this);
            
        },
        
        cancelbooking: function() {
            
            var Y = this.Y;
            uri = this.domain+'/local/zilink/plugins/bookings/rooms/action.php';
            if (this.xhr != null) {
                this.xhr.abort();
            }

            this.xhr = Y.io(uri, {
                data: 'action=bookings_rooms_cancel_booking&id='+this.id+'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {
                           results = Y.Node.create(o.responseText);
                           Y.one('#zilink_bookings_rooms_current_bookings_container').replace(results);
                           results.setAttribute('id', 'zilink_bookings_rooms_current_bookings_container');
                    },
                    failure: function(id, o) {
                    },
                    end: function (id, o) {
                    }
                }
            });
        }
},



M.local_zilink_bookings_rooms_maintenance = {
        
        
        init: function(Y, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.room = '';
            
            Y.one('#zilink_bookings_rooms_booked_room').on('change', function(e) {
                e.preventDefault();
                
                var sel;
                var opt;
                                
                sel = document.getElementById('zilink_bookings_rooms_booked_room');
                opt = sel.options[sel.selectedIndex];
                this.room = opt.value; 
                this.updatedates();
            }, this);
            
        },  
        
        updatedates: function() {
            
            var Y = this.Y;
            uri = 'action.php';
            if (this.xhr != null) {
                this.xhr.abort();
            }
            
            Y.one('#zilink_timetableupdateprogress').setStyle('visibility', 'visible');
            Y.one('#zilink_timetableupdateprogress').setStyle('width', '10px');
            
            Y.one('#zilink_timetableupdatefailed').setStyle('visibility', 'hidden');
            Y.one('#zilink_timetableupdatesuccess').setStyle('visibility', 'hidden');

            this.xhr = Y.io(uri, {
                data: 'action=roombooking_room_maintenance_form_update&room='+this.room+'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {
                       results = Y.Node.create(o.responseText);
                       Y.one('#zilink_roombooking_room_maintenance').replace(results);
                       results.setAttribute('id', 'zilink_roombooking_room_maintenance');
                       
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
},

M.local_zilink_bookings_rooms_maintenance_cancel_booking = {
        
        init: function(Y, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.id = '';
            
            Y.one('#zilink_bookings_rooms_maintenance_current_bookings').on('click', function(e) {
                e.preventDefault();
                
                this.id = e._event.target.getAttribute("name").toString();
                this.cancelbooking();
                
            }, this);
            
        },
        
        cancelbooking: function() {
            
            var Y = this.Y;
            uri = 'action.php';
            if (this.xhr != null) {
                this.xhr.abort();
            }

            this.xhr = Y.io(uri, {
                data: 'action=roombooking_maintenance_cancel_booking&bookingid='+this.id+'&sesskey='+this.sesskey,
                context: this,
                on: {
                    success: function(id, o) {
                       results = Y.Node.create(o.responseText);
                       Y.one('#zilink_bookings_rooms_maintenance_current_bookings_container').replace(results);
                       results.setAttribute('id', 'zilink_bookings_rooms_maintenance_current_bookings_container');
                    },
                    failure: function(id, o) {
                    }
                }
            });
        }
}