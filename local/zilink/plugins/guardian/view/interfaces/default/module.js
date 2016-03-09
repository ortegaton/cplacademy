M.local_zilink_guardian_view_attendance_overview_chart = {
        
        init: function(Y, myDataValues, maxValue, sesskey) {
            this.Y = Y;
              
            var myTooltip = {
                    markerLabelFunction: function(categoryItem, valueItem, itemIndex, series, seriesIndex)
                    {
                        var msg = M.util.get_string(valueItem.displayName, 'local_zilink') + ' ' + valueItem.value + ' days this term';
                        return msg; 
                    }
                };
            
            var chartAxes = {
                    yAxis: {
                      keys:             ['present','late','authorisedabsence','unauthorisedabsence'],
                      position:         "left",
                      type:                "stacked",
                      //type:             "numeric",
                      maximum:            maxValue,
                      minimum:            0,
                      roundingMethod:     5,
                      alwaysShowZero:     false,
                      styles:{
                        majorUnit: {
                              count: (maxValue / 5) +1
                        },
                        majorTicks: {
                          display: "none"
                        },
                        label: { rotation: 0,    color:"#000000" }
                      },
                      labelFormat: {
                        thousandsSeparator: "."
                      },
                      labelFunction: function(val, format)
                      {
                        return (val/5);
                      },
                      type:"stacked"
                 
                    },
                    xAxis: {
                      keys: ["category"],
                      position: "bottom",
                      type: "category",
                      styles:{
                        majorTicks: {
                            display: "none"
                        },
                        label: { rotation: 0,    color:"#000000" }
                      }
                    }
                  };
            
            var attendance_overview = new Y.Chart({    
                
                render:                "#zilink_guardian_view_attendance_overview_chart",
                dataProvider:        myDataValues,
                type:                 "column",
                stacked:            true,
                axes:                chartAxes,
                horizontalGridlines:true,
                verticalGridlines:     true,
                styles : { series: {
                        present : { marker: { fill : {color: "#006600" }, width: 300 / myDataValues.length } },
                        late : { marker: { fill : {color: "#FF9900" }, width: 300 / myDataValues.length } },
                        authorisedabsence : { marker: { fill : {color: "#6666FF" }, width: 300 /myDataValues.length } },
                        unauthorisedabsence : { marker: { fill : {color: "#FF0000" }, width: 300 /myDataValues.length } }
                } },
                tooltip:             myTooltip
               });
            
        }
},

M.local_zilink_guardian_view_assessment_overview_chart = {
        
        init: function(Y, myDataValues, minValue, maxValue,grades,resultType,extlinks,sesskey) {
            this.Y = Y;  
            
            
            var myAssessmentOverviewTooltip = {
                    markerLabelFunction: function(categoryItem, valueItem, itemIndex, series, seriesIndex)
                    {
                        var msg = resultType[valueItem.displayName] + ' - ' + grades[valueItem.value];
                        return msg; 
                    }
                };
            
            var chartAxes = {
                    yAxis: {
                      keys:             ['attainment','targets','progress'],
                      position:         "left",
                      type:                "column",

                      maximum:            maxValue,
                      minimum:            minValue,
                      roundingMethod:     2,
                      alwaysShowZero:     false,
                      styles:{
                        majorUnit: {
                              count: ((maxValue - minValue) /2) +1
                        },
                        majorTicks: {
                          display: "none"
                        },
                        label: { rotation: 0,    color:"#000000" }
                      },
                      labelFormat: {
                        thousandsSeparator: "."
                      },
                      type:"stacked"
                 
                    },
                    xAxis: {
                      keys: ["category"],
                      position: "bottom",
                      type: "category",
                      styles:{
                        majorTicks: {
                            display: "none"
                        },
                        label: { rotation: 45,    color:"#000000" }
                      }
                    }
                  };
            
            var itemcount = 0;

            for(var i in myDataValues)
            {
                itemcount++;
            }
            
            var assessment_overview = new Y.Chart({    
                
                dataProvider:        myDataValues,
                type:                 "column",
                horizontalGridlines:true,
                verticalGridlines:     true,
                styles : { series: {
                    attainment : { marker: { fill : {color: "#000066" }, width: 200 / itemcount } },
                    targets : { marker: { fill : {color: "#FF0000" }, width: 200 / itemcount } }
                } },
                axes:                chartAxes,
                tooltip:             myAssessmentOverviewTooltip
               });

            mynumericaxis = assessment_overview.getAxisByKey('yAxis');
            mynumericaxis.set("labelFunctionScope", assessment_overview);
            mynumericaxis.set("labelFunction", function(value)
            {
                 return grades[value];
            });
            
            assessment_overview.on("markerEvent:click", function(e) {
                    
                count = 0;
                          
                var str;
                for (var key in e.categoryItem)
                {
                    if(count == 2)
                    {
                        str = e.categoryItem[key];
                        str = str.replace(' ', '');
                        str = str.toLowerCase();
                        
                        if(typeof( extlinks[str]) != 'undefined')
                        {
                            window.location = extlinks[str];
                        }
                    }
                    count++;
                }
            });
            
            assessment_overview.render("#zilink_guardian_view_assessment_overview_chart");
            
        }
},

M.local_zilink_guardian_view_reports = {
        
        init: function(Y, domain,courseid,offset, sesskey) {
            this.Y = Y;
            this.sesskey = sesskey;
            this.xhr = null;
            this.domain = domain;
            this.reportid  = 0;
            this.courseid = courseid;
            this.offset = offset;
            
            Y.all('#zilink_view_reports').on('click', function(e) {
                e.preventDefault();
                
                this.reportid = e.currentTarget.getAttribute("name").toString();
                this.viewreport();
            }, this);
            
        },
        
        viewreport: function() {
            
            window.open(this.domain+'/local/zilink/plugins/guardian/view/interfaces/default/pages/reports.php?action=view&cid='+this.courseid +'&rid='+ this.reportid +'&offset='+this.offset+'&sesskey='+this.sesskey,"_blank",'width=900');
        }
};