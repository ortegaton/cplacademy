var jQueryRMC = jQuery.noConflict();


jQueryRMC(document).ready(function(){
	jQueryRMC("#id_search_fts").tooltipster();
	jQueryRMC("#fts_search_button").tooltipster();
	if(search_result.length > 0) {
		jQueryRMC("#search-result").kendoGrid({
		    dataSource: {
		        data: search_result,
		           pageSize: 10
		        
		    },
		    groupable: false,
		    pageable: {
		        refresh: false,
		        pageSizes: true,
		        /*buttonCount: 5,*/
		        messages: {
		            empty: "No items found"
		        }
 		    },
 		    sortable: false,
		    dataBound: function(arg) {
		    	jQueryRMC(".embed_gen_url").each(function(){
					jQueryRMC(this).on('click', function(){
						var node_id = jQueryRMC(this).attr('data-id');
						var post_url = base_path + '/mod/rmc/get_embed_url.php';
						var p_data = {
								'node_id' : node_id
								
						};
						jQueryRMC.ajax({
							type: 'POST',
							url: post_url,
							data: p_data,
							success:function(data){
							jQueryRMC("#embed_popup").html(data);
							jQueryRMC.fancybox({href:'#embed_popup'});
							}
						});
					});
				});
		    },
		    rowTemplate: kendo.template(jQueryRMC("#searchRowTemplate").html()),
		    columns : [{
		    	headerAttributes: {
		            style: "display: none"
		        }
		    }
		               ]
		});
		jQueryRMC("#search-load-image").hide();
	}
});
