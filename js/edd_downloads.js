/*function edd_downloads_upgrade_button(id,button_id,license_id){

	var $JS		= jQuery.noConflict();

	

	if(id > 0){

		//update the database

		$JS.ajax({

			url: upgrade.ajaxurl,

			type: 'post',

			dataType: 'json',

			data: ({action : 'ShowUpgradeButton', id : id}),

			success: function(response){

				if(response.error){

					//display error msg

					$JS('#upgrade_button_'+button_id).html(response.error);

				}else if(response.message){

					//proceed - no error message

					$JS('#upgrade_button_'+button_id).html(response.message);

					$JS('#edd_purchase_'+id).append('<input type="hidden" name="upgrade_license" value="'+license_id+'" />');

					$JS('#edd_purchase_'+id).append('<input type="hidden" name="upgrade_product_to" value="'+id+'" />');					

				}

			}

		});	

	}

}



jQuery(document).load(function ($) {

	$("#upgrade_button_523").children().hide(); 

});*/



function edd_downloads_hide_all_buttons(button_id){

	var $JS		= jQuery.noConflict();

	$JS("#upgrade_button_"+button_id).children().hide();

}



function edd_downloads_show_button(id,button_id,license_id,old_product){

//console.log('#edd_purchase_'+id+"_"+button_id);

	var $JS		= jQuery.noConflict();

	$JS("#upgrade_button_"+button_id).children().hide();

	

	$JS("#edd_purchase_"+id+"_"+button_id).show();



	if(document.getElementById(id+'_'+button_id+'_old_product')){

		document.getElementById(id+'_'+button_id+'_old_product').value = old_product;

	}else{

		$JS('#edd_purchase_'+id+"_"+button_id).append('<input type="hidden" name="old_product" id="'+id+"_"+button_id+'_old_product" value="'+old_product+'" />');

	}



	if(document.getElementById(id+'_'+button_id+'_upgrade_license')){

		document.getElementById(id+'_'+button_id+'_upgrade_license').value = license_id;

	}else{

		$JS('#edd_purchase_'+id+"_"+button_id).append('<input type="hidden" name="upgrade_license" id="'+id+"_"+button_id+'_upgrade_license" value="'+license_id+'" />');

	}

	

	if(document.getElementById(id+'_'+button_id+'_upgrade_product_to')){

		document.getElementById(id+'_'+button_id+'_upgrade_product_to').value = id;

	}else{

		$JS('#edd_purchase_'+id+"_"+button_id).append('<input type="hidden" name="upgrade_product_to" id="'+id+"_"+button_id+'_upgrade_product_to" value="'+id+'" />');	

	}

}



jQuery(document).ready(function ($) {

	$(document).off( 'click', '#edd_purchase_form #edd_login_fields input[type=submit]', function(e) {

	});

});



function edd_downloads_edd_print_error_line(error_id,error,error_title,field_id){

	var $JS		= jQuery.noConflict();

	//$JS('#edd_error_' + error_id).remove();

	//$JS('#edd_purchase_submit').before('<p class="edd_error" id="edd_error_' + error_id + '"><strong>' + error_title + '</strong>: ' + error + '</p>');
	
	if($JS('#edd_error_' + error_id).length == 0){

		$JS('#'+field_id).before('<p class="edd_error" id="edd_error_' + error_id + '"><strong>' + error_title + '</strong>: ' + error + '</p>');	

	}
	
/*	if ( ('logged_in_only' == error_id || 'registration_required' == error_id) && $JS('#edd_error_' + error_id).length == 0) {

		$JS('#'+field_id).before('<p class="edd_error" id="edd_error_' + error_id + '"><strong>' + error_title + '</strong>: ' + error + '</p>');

	} else if($JS('#edd_error_' + error_id).length == 0){

		$JS('input[name="'+field_id+'"]').before('<p class="edd_error" id="edd_error_' + error_id + '"><strong>' + error_title + '</strong>: ' + error + '</p>');	

	}*/

}

function check_domain(product_id){

	var $JS		= jQuery.noConflict();

	var domain = $JS("#edd_downloads_domains_domain_"+product_id).val();

	$JS("#edd_downloads_domains_domain_"+product_id+"_loading").css("display", "block");

	$JS.ajax({ 

		data: ({action: 'edd_downloads_check_domain', edd_downloads_domains_domain:domain}),

		type: 'post',

		dataType: 'json',		 

		url: edd_downloads_scripts.ajaxurl,

		success: function(response){

			if(response.error){

				//display error msg

				$JS("#edd_downloads_domains_domain_"+product_id+"_loading").css("display", "none");

				alert('Invalid domain. Please re-enter it in this format: mysite.com');	

				return false;

			}else if(response.message){

				//proceed - no error message

				//console.log("#edd_downloads_domains_domain_form_"+product_id);

				$JS("#edd_downloads_domains_domain_"+product_id).val(domain);

				$JS( "form#edd_downloads_domains_domain_form_"+product_id ).submit();

				return true;

			}

		}

	});

	return false;

}



function edd_downloads_hide_all_buttons(button_id){

	var $JS		= jQuery.noConflict();

	$JS("#upgrade_button_"+button_id).children().hide();

}



/*function edd_downloads_show_button(id,button_id,license_id){

	var $JS		= jQuery.noConflict();

	$JS("#upgrade_button_"+button_id).children().hide();

	

	$JS("#edd_purchase_"+id+"_"+button_id).show();

}*/