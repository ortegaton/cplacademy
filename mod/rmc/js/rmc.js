var jQueryRMC = jQuery.noConflict();
jQueryRMC(document).ready(function() {
	jQueryRMC("#buy").on('click', '', function(){
		jQueryRMC.fancybox({href:'#buy_popup'});
    });
	
	jQueryRMC("#adv-search").show();
	
	jQueryRMC("#id_advanced_search").addClass("collapsed");
	
	jQueryRMC('#term-popup').on('click', '', function(){
		jQueryRMC.fancybox({href:'#term-cond',width: 800, height: 800,afterClose: function(){
			jQueryRMC('#buy').trigger('click');
		}});
		
		
	});
	
	jQueryRMC("#preview-button").on('click', '', function(){
		var url = jQueryRMC(this).attr("data-url");
		window.open(url,'Preview Content','directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=yes,width=800,height=600');
		window.moveTo(0,0);
		//window.resizeTo(800, 600);
	});
	
	

	
	
	
	jQueryRMC("#bt_buy_popup").on('click', '', function(){
		var item = jQueryRMC("#item").text();
		var link_to_item = jQueryRMC("#link_to_item").text();
		var selector = jQueryRMC("#selector").text();
		var course_name = jQueryRMC("#course_name").text();
		var publisher = jQueryRMC("#publisher").text();
		var publisher_id = jQueryRMC("#publisher_id").val();
		var organisation = jQueryRMC("#organisation").text();
		var authorise_mail = jQueryRMC("#authorise_mail").val();
		var enrol_count = jQueryRMC("#enrol_count").val();
		var agreement = jQueryRMC("#agreement").val();
		var cost = jQueryRMC("#cost").text();
		var node_id = jQueryRMC("#node_id").val();
		var course_id = jQueryRMC("#course_id").val();
		var alfresco_share_url = jQueryRMC("#alfresco_share_url").val();
		var user_count = jQueryRMC("#no_licenses").val();
		var licence_chk = jQueryRMC("#licence_chk").val();
		var publisher_email = jQueryRMC("#publisher_email").val();
		var intRegex = /^\d+$/;
		if(!jQueryRMC("#licence_chk").is(':checked')) {
			alert("Please agree to the licence");
			return false;
		}
		var section = jQueryRMC("#section").val();
		var cmid = jQueryRMC("#cmid").val();
		var sr = jQueryRMC("#sr").val();
		var purchase_id = jQueryRMC("#purchase_id").val();
		if(authorise_mail == "") {
			alert("Please enter email id");
			return false;
		}
		else {
			var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if (!filter.test(jQueryRMC("#authorise_mail").val())) {
				alert("Please enter a valid email id");
				jQueryRMC("#authorise_mail").focus()
				return false;
			}
		}
		if(isNaN(user_count)) {
			alert('Please enter a valid value.');
			return false;
		} else if(parseInt(user_count) <= 0) {
			alert('Number of licences should be greater than 0');
			return false;
		}
		//jQueryRMC("#message").html("Mail is being sent......");
		var post_data = {'item' : item, 'link_to_item' : link_to_item, 'selector' : selector, 'course_name' : course_name, 'publisher' : publisher, 
						 'organisation' : organisation, 'authorise_mail' : authorise_mail, 'agreement' : agreement, 'cost' : cost, 'node_id' : node_id, 'purchase_id' : purchase_id,
						 'course_id' : course_id, 'alfresco_share_url' : alfresco_share_url, 'section' : section, 'cmid' : cmid, 'sr' : sr, 'publisher_id' : publisher_id, 
						 'enrol_count' : enrol_count, 'user_count' : user_count, 'publisher_email' : publisher_email};
		
		jQueryRMC.ajax({
			  type: "POST",
			  url: './rmc_send_mail.php',
			  data: post_data,
			  success: function(data){
				  jQueryRMC("#message").html("");
				  //console.log(data);
				  if((data.indexOf('error') != -1) || data.indexOf('Error') != -1) {
					  alert("There was problem sending mail. Please try again.");
				  } else {
					  window.location.href = data;
				  }
			  }
		});
	});
	
	jQueryRMC("#bt_cancel_popup").on('click', '', function(){
		jQueryRMC.fancybox.close(true);
	});
	
	
	
	
	function ajaxLoader (el, options) { 
	// Becomes this.options
	var defaults = {
		bgColor 		: '#fff',
		duration		: 800,
		opacity			: 0.7,
		classOveride 	: false
	}
	this.options 	= jQuery.extend(defaults, options);
	this.container 	= jQueryRMC(el);
	
	this.init = function() {
		var container = this.container;
		// Delete any other loaders
		this.remove(); 
		// Create the overlay
		var overlay_width = jQueryRMC( document ).width();
		var overlay_height = jQueryRMC( document ).height()
		var overlay = jQueryRMC('<div></div>').css({
				'background-color': this.options.bgColor,
				'opacity':this.options.opacity,
				'width':overlay_width,
				'height':overlay_height,
				'position':'absolute',
				'top':'0px',
				'left':'0px',
				'z-index':100000000
		}).addClass('ajax_overlay');
		// add an overiding class name to set new loader style 
		if (this.options.classOveride) {
			overlay.addClass(this.options.classOveride);
		}
		// insert overlay and loader into DOM 
		container.append(
			overlay.append(
				jQueryRMC('<div id="ajax_div"><img class="ajax_loader_image" src="'+ img_data +'" /></div>').addClass('ajax_loader')
			).fadeIn(this.options.duration)
		);
    };
	
	this.remove = function(){
		var overlay = this.container.children(".ajax_overlay");
		if (overlay.length) {
			overlay.fadeOut(this.options.classOveride, function() {
				overlay.remove();
			});
		}	
	}

    this.init();
}	
	
	jQueryRMC("#preloader").on('click','' , function(){	
		box1 = new ajaxLoader(this,  {classOveride: 'blue-loader', bgColor: '#000', opacity: '0.3'});
		box1.init();
	});
	
	jQueryRMC( "#id_search_string" ).on('keydown', function(event){
		if ( event.keyCode == 13 ) {
			event.preventDefault();
			fetch_search_results();
		}
		
	});

});

function check_login() {
	post_data = {'method' : 'check_login', 'course' : jQueryRMC("#course").val(), 'section' : jQueryRMC("#section").val(), 'cmid' :  jQueryRMC("#cmid").val()};
	jQueryRMC.ajax({
		type: "POST",
		url: './ajax_callback.php',
		data: post_data,
		success: function(data){
			if(data != 'yes') {
				alert('Your session has timed out. You will now be redirected to the login page.');
				window.location.href = data;
			}
		},
		dataType: 'text'
	});
}

function fetch_search_results(search_type) {
	check_login();
	var search_string = jQueryRMC('#id_search_string').val();
	var add = jQueryRMC("#add").val();
	var course = jQueryRMC("#course").val();
	var section = jQueryRMC("#section").val();
	var cmid = jQueryRMC("#cmid").val();
	var submitbutton = jQueryRMC("#submitbutton").val();
	var publisher = jQueryRMC("#id_publisher").val();
	var discipline = jQueryRMC("#id_discipline").val();
	var train_package = jQueryRMC("#id_training_package").val();
	var resource_type = jQueryRMC("#id_resource_type").val();
	var post_data = {
			'search_string' : search_string,
			'add' : add,
			'course' : course,
			'section' : section,
			'cmid' : cmid,
			'search_type' : search_type,
			'publisher' : publisher,
			'discipline' : discipline,
			'training_package' : train_package,
			'resource_type' : resource_type,
			'submitbutton' : submitbutton
	};
	jQueryRMC("#id_advanced_search").addClass("collapsed");
	jQueryRMC.ajax({
		type: "POST",
		url: './fetch_search_result_ajax.php',
		data: post_data,
		success: function(data){
			window.onbeforeunload = null;
			
			jQueryRMC("#search-result-message").html('');
			jQueryRMC("#search-result-message").html(data.message);
			if(data.nodes.length > 0) {
				jQueryRMC("#search-result").kendoGrid({
				    dataSource: {
				        data: data.nodes,
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
			} else {
				jQueryRMC("#search-result").html('');
			}
		},
		dataType: 'json'
	});
}


jQueryRMC('#mform1').unbind('submit');
jQueryRMC( document ).ajaxStart(function() {
	jQueryRMC.fancybox.close(true);
	jQueryRMC("#preloader").trigger('click');
});

jQueryRMC(document).ajaxStop(function() {
	box1.remove();
});
jQueryRMC("#mform1").on('submit', function(){
	//jQueryRMC("#preloader").trigger('click');
	return false;
});
