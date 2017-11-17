/*!
 * myongkir.js v1.1.0
 * Copyright 2014, eezhal92
 *
 * Freely distributable under the MIT license.
 * jQuery should be loaded in order to use this script
 *
 * http://eezhal92.com
 */

jQuery(document).ready(function($) {
    var $billingCity = $('#billing_city');
    var $billingState = $('#billing_state');
    var $shippingCity = $('#shipping_city');
    var $shippingState = $('#shipping_state');

    $billingCity.prop('disabled', true).select2();
    $shippingCity.prop('disabled', true).select2();

    var userBillingCity = myongkirAjax.billing_city || $billingCity.val();
    var userShippingCity = myongkirAjax.shipping_city || $shippingCity.val();

    var requests = {};

    removeFieldsClass();

    function getCitiesSuccessCallbackFactory (elementId) {
        return function (response) {
            var parsedResponse = jQuery.parseJSON(response);
            var cities = Object.keys(parsedResponse)
                .map(function (city) {
                    return { value: city, text: parsedResponse[city] };
                });

            cities.forEach(function (city) {
                $('<option/>', city).appendTo(elementId);
            });

            $(elementId).select2('enable', true);
            $(elementId).prop('disabled', false);
            $(elementId).val(cities[0].value).trigger('change');
        }
    }


    $billingState.live('change', function() {
        var billingStateId = $(this).find('option:selected').val();


        if (billingStateId) {
            $('s2id_billing_state').empty();
            $billingCity.select2('enable', false);
            $billingCity.empty();

            getCities('#billing_city', billingStateId)
                .then(getCitiesSuccessCallbackFactory('#billing_city'));
        }
    });

    $shippingState.live('change', function() {
        var shippingStateId = $(this).find('option:selected').val();


        if(shippingStateId) {
            $('s2id_shipping_city').empty();
            $shippingCity.select2('enable', false);
            $shippingCity.empty();

            getCities('#shipping_city', shippingStateId, null)
                .then(getCitiesSuccessCallbackFactory('#shipping_city'));
        }
    });

    function getCities(cityElementId, userStateId) {
        requests[cityElementId] = $.ajax({
            url: myongkirAjax.ajax_url,
            type: 'get',
            data: {
                'action': 'get_cities',
                'nonce': myongkirAjax.nonce,
                'state': userStateId
            },
            beforeSend : function () {
                if(requests[cityElementId] != null) {
                    requests[cityElementId].abort();
                }
            },
        });

        return requests[cityElementId];
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
