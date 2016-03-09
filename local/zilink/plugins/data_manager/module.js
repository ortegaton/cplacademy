M.local_zilink_data_manager_sessions = {
        
        init: function(Y, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.offset = 0;

            /*
            Y.one('#id_data_manager_sessions_allowed').on('change', function(e) {
                e.preventDefault();
                
                var selAllowed;
                var selOrder;
                
                selAllowed = document.getElementById('id_data_manager_sessions_allowed');
                selOrder = document.getElementById('id_sessions_order');
                   
                for (var i = 0; i < selAllowed.length; i++) {
                    if (selAllowed.options[i].selected) 
                    {
                        try {
                            var found = false;
                            for (var ii = 0; ii < selOrder.length; ii++) 
                            {
                                if (selOrder.options[ii].value == selAllowed.options[i].value) 
                                {
                                    found = true;
                                }
                            }
                            if(!found)
                            {
                                selOrder.add(selAllowed.options[i].cloneNode(true), null);
                            }
                            
                        }
                        catch(ex) {
                            var found = false;
                            for (var ii = 0; ii < selOrder.length; ii++) 
                            {
                                if (selOrder.options[ii].value == selAllowed.options[i].value) 
                                {
                                    found = true;
                                }
                            }
                            if(!found)
                            {
                                selOrder.add(selAllowed.options[i].cloneNode(true));
                            }
                        }
                    }
                }
                for (var i = 0; i < selAllowed.length; i++) {
                    if (selAllowed.options[i].selected == false)
                    {
                        for (var ii = 0; ii < selOrder.length; ii++) 
                        {
                            if (selOrder.options[ii].value == selAllowed.options[i].value) 
                            {
                                selOrder.remove(ii);
                            }
                        }
                    } 
                }

            }, this);
            */
            
            Y.one('#id_assessment_session_up').on('click', function(e) {
                e.preventDefault();
                
                var increment = -1;
                
                var selOrder = document.getElementById('id_data_manager_sessions_allowed');
                var selIndex = selOrder.selectedIndex;
                
                if(-1 == selIndex) {
                    return;
                }
                        
                if((selIndex + increment) < 0 || (selIndex + increment) > (selOrder.options.length-1)) {
                    return;
                }
                    
                   var selValue = selOrder.options[selIndex].value;
                   var selText = selOrder.options[selIndex].text;
                   selOrder.options[selIndex].value = selOrder.options[selIndex + increment].value
                   selOrder.options[selIndex].text = selOrder.options[selIndex + increment].text
 
                 selOrder.options[selIndex + increment].value = selValue;
                   selOrder.options[selIndex + increment].text = selText;
                selOrder.selectedIndex = selIndex + increment;
        
                var saveSelOrder;
                
                for (var i = 0; i < selOrder.length; i++) {
                    if(i > 0)
                    {
                        saveSelOrder = saveSelOrder + "," + selOrder.options[i].value;
                    }
                    else 
                    {
                        saveSelOrder = selOrder.options[i].value;
                    }
                    
                    
                }
                document.getElementById("id_sessions_order").value = saveSelOrder;
                
            }, this);
            
            Y.one('#id_assessment_session_down').on('click', function(e) {
                e.preventDefault();
                
                var increment = 1;
                
                var selOrder = document.getElementById('id_data_manager_sessions_allowed');
                var selIndex = selOrder.selectedIndex;
                
                if(-1 == selIndex) {
                    return;
                }
                        
                if((selIndex + increment) < 0 || (selIndex + increment) > (selOrder.options.length-1)) {
                    return;
                }
                    
                   var selValue = selOrder.options[selIndex].value;
                   var selText = selOrder.options[selIndex].text;
                   selOrder.options[selIndex].value = selOrder.options[selIndex + increment].value;
                   selOrder.options[selIndex].text = selOrder.options[selIndex + increment].text;
 
                 selOrder.options[selIndex + increment].value = selValue;
                   selOrder.options[selIndex + increment].text = selText;
                selOrder.selectedIndex = selIndex + increment;
                
                var saveSelOrder;
                
                for (var i = 0; i < selOrder.length; i++) {
                    if(i > 0)
                    {
                        saveSelOrder = saveSelOrder + "," + selOrder.options[i].value;
                    }
                    else 
                    {
                        saveSelOrder = selOrder.options[i].value;
                    }
                    
                    
                }
                document.getElementById("id_sessions_order").value = saveSelOrder;
                
            }, this);
            /*
            Y.one('#id_submitbutton').on('click', function(e) {

                var selOrder = document.getElementById('id_data_manager_sessions_allowed');
                var saveSelOrder;
                
                for (var i = 0; i < selOrder.length; i++) {
                    if(i > 0)
                    {
                        saveSelOrder = saveSelOrder + "," + selOrder.options[i].value;
                    }
                    else 
                    {
                        saveSelOrder = selOrder.options[i].value;
                    }
                    document.getElementsByName("sessions_order").value = saveSelOrder;
                    
                }
                alert(document.getElementsByName("sessions_order").value);
                
            }, this);
            
            */
        }
}