/*!
 * myongkir.js v1.0.1
 * Copyright 2014, eezhal92
 *
 * Freely distributable under the MIT license.
 * this script using jQuery
 *
 * http://eezhal92.com
 */

jQuery(document).ready(function( $ ) {
	
	// alert('welcome to myongkir.js');

	// initialization
	$('#billing_city').prop('disabled', true).select2();
	$('#shipping_city').prop('disabled', true).select2();	

	var user_billing_city = myongkirAjax.billing_city;
	var user_shipping_city = myongkirAjax.shipping_city;
	
	var user_billing_state = $('#billing_state').val();
	var user_shipping_state = $('#shipping_state').val();

	var curRequest = {}; 

	removeFieldsClass();

	if( user_billing_state ) {
		// alert(user_billing_state);
		getCities('#billing_city', user_billing_state, user_billing_city );
	}

	if( user_shipping_state ) {
		// alert(user_shipping_state);
		getCities('#shipping_city', user_shipping_state, user_shipping_city );
	}
	

	$('#billing_state').live('change', function() {
		var b_state_id = $(this).find('option:selected').val();									

		$('s2id_billing_state').empty();												
		$('#billing_city').select2("enable", false);

		if( b_state_id ) {
			$('#billing_city').empty();
			getCities( '#billing_city', b_state_id, null);
		}
	});

	$('#shipping_state').live('change', function() {
		var s_state_id = $(this).find('option:selected').val();									

		$('s2id_shipping_city').empty();												
		$('#shipping_city').select2("enable", false);

		if( s_state_id ) {
			$('#shipping_city').empty();
			getCities( '#shipping_city', s_state_id, null);
		}
	});


	// request function
	function getCities( city_element, user_state_id, user_city_id) {			
		
		curRequest[city_element] = $.ajax({
	      	url: myongkirAjax.ajax_url,
	      	type: 'get',
	      	data: {
				'action': 'get_cities',
				'nonce': myongkirAjax.nonce,
				'state': user_state_id
			},
			beforeSend : function()    {           
	            if(curRequest[city_element] != null) {
	                curRequest[city_element].abort();
	            }
	        },			      							      
	      	success: function( data ) { // return string
		      	// alert(data);
		      	data = jQuery.parseJSON(data);
		      	
		      	$.each( data, function (city_id, city_name) {		          				
					$("<option/>",{ value:city_id, text:city_name }).appendTo(city_element);
				});
					
		      	$(city_element).select2("enable", true).val(user_city_id);
	      	}
	    });
	}

	// Page updated only when city fields is changed
    function removeFieldsClass() {
		$('#billing_address_1_field').removeClass('address-field ');
		$('#billing_state_field').removeClass('address-field ');
		$('#billing_postcode_field').removeClass('address-field ');
		$('#shipping_address_1_field').removeClass('address-field ');
		$('#shipping_state_field').removeClass('address-field ');
		$('#shipping_postcode_field').removeClass('address-field ');
    } 

});
