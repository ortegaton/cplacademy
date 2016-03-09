var jQueryRMC = jQuery.noConflict();
jQueryRMC(document).ready(function(){
	jQueryRMC('#rmc_generate_url').on('click', function(){
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

