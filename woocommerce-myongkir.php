<?php
/**
 * Plugin Name: MyOngkir
 * Plugin URI: http://eezhal92.com/myongkir/
 * Description: WooCommerce add-on for indonesian local shipping.
 * Version: 1.0.1
 * Author: eezhal
 * Author URI: http://eezhal92.com
 * Contributor: xhijack
 * Contributor URL: ramdani.sopwer.net
 * Requires at least: 3.8
 * @package myongkir
 * @category Core
 * @author eezhal
 * Last Updated: 20 December 2015
 */
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	register_activation_hook( __FILE__, 'check_base_country' );
	register_activation_hook( __FILE__, 'check_currency' );

	add_action( 'init', 'global_myongkir_init' ); //  try to change scope to more global
	add_action( 'woocommerce_shipping_init', 'myongkir_shipping_method_init' );
	add_filter( 'woocommerce_shipping_methods', 'add_myongkir_shipping_method' );
	add_filter( 'woocommerce_general_settings', 'add_api_key_setting' );

	add_filter( 'woocommerce_checkout_fields', 'woocommerce_myongkir_checkout_fields' );
	add_filter( 'woocommerce_billing_fields', 'custom_woocommerce_billing_fields' );
	add_filter( 'woocommerce_shipping_fields', 'custom_woocommerce_shipping_fields' );
	add_action( 'woocommerce_checkout_update_order_meta', 'woocommerce_myongkir_checkout_field_update_order_meta' );
	add_filter( 'woocommerce_my_account_my_address_formatted_address', 'woocommerce_myongkir_my_account_my_address_formatted_address', 10, 2 );

	add_action( 'wp_footer', 'get_cities_js' );
	add_action( 'wp_ajax_get_cities', 'get_cities_cb' );
	add_action( 'wp_ajax_nopriv_get_cities', 'get_cities_cb' );

	function check_base_country() {
		global $woocommerce;

		if ( $woocommerce->countries->get_base_country() !== 'ID' ) {
    		$exit_msg = 'Plugin MyOngkir Shipping only based location must be Indonesia.';
    		exit($exit_msg);
		}
	}

	function check_currency() {
		$current_currency = get_woocommerce_currency();

		if( $current_currency != 'IDR' ) {
			$exit_msg = 'Plugin MyOngkir Shipping must use IDR currency.';
			exit($exit_msg);
		}
	}

	function global_myongkir_init() {
		require_once(plugin_dir_path( __FILE__ ) . 'class/myongkir-shipping.php');

		// define nonce constans
		define("MYONGKIR_NONCE", "myongkir-nonce");

		$myongkir_shipping = MyOngkir_Shipping::get_instance();

		$rajaongkir_api_key = get_option('woocommerce_rajaongkir_api_key');
		$myongkir_shipping->set_api_key( $rajaongkir_api_key );

		$GLOBALS['myongkir'] = $myongkir_shipping;
	}

	function myongkir_shipping_method_init() {
		if ( ! class_exists( 'MyOngkir_Shipping_Method' ) ) {
			require "myongkir-shipping.php";
		}
	}

	function add_myongkir_shipping_method( $methods ) {
		$methods[] = 'MyOngkir_Shipping_Method';
		return $methods;
	}

	function add_api_key_setting( $settings ) {
	  $updated_settings = array();

	  foreach ( $settings as $section ) {
	    // at the bottom of the General Options section
	    if ( isset( $section['id'] ) && 'general_options' == $section['id'] &&
	       isset( $section['type'] ) && 'sectionend' == $section['type'] ) {

	      $updated_settings[] = array(
	        'name'     => __( 'RajaOngkir API Key', 'wc_seq_order_numbers' ),
	        'id'       => 'woocommerce_rajaongkir_api_key',
	        'css'	   => 'width: 300px;',
	        'type'     => 'text',
	        'desc'     => __( 'Ensure that the api key is correct and save this setting before choose your base city. <br> If you don\'t have any, <a href="'. esc_url('http://rajaongkir.com/akun/daftar') .'">register here</a>. ' ),
	      );
	    }

	    $updated_settings[] = $section;
	  }

	  return $updated_settings;
	}

	// reorder form field
	function woocommerce_myongkir_checkout_fields( $fields ) {
		$allowed_fields = array('billing', 'shipping');

		// delete billing and shipping city fields
		unset($fields['billing']['billing_city']);
		unset($fields['shipping']['shipping_city']);

		foreach( $fields as $type => $field )
		{
			if( in_array($type, $allowed_fields) )
			{
				// set billing address css class
				$fields[$type][$type.'_address_1']['class'] = array('form-row-wide');
				$fields[$type][$type.'_address_2']['class'] = array('form-row-wide');
				unset($fields[$type][$type.'_address_2']['label_class']);

				// modify postcode field css class
				$postcode_field  = $fields[$type][$type.'_postcode'];
				$postcode_field['class'] = array('form-row-last');
				$postcode_field['clear'] = true;

				// offset value of state field
				$offset_state = array_search($type.'_state', array_keys($fields[$type]));
				$offset_after_state  = $offset_state + 1;

				$fields[$type] =
					// country, first name | last name, company, address1, address2, state(provinsi) |
					array_slice($fields[$type], 0, $offset_after_state, true) +
					// postcode / zip, city
					array($type.'_postcode' => $postcode_field) + array($type.'_city' => create_city_field($type) ) +
					// email | phone
					array_slice($fields[$type], $offset_after_state, null, true);
			}
		}

		$fields['billing']['selected_courier'] = array(
			'type'       => 'select',
			'label'      => __('Courier', 'woocommerce'),
		    'placeholder'=> _x('Courier', 'placeholder', 'woocommerce'),
		    'required'   => true,
		    'class'      => array('form-row-wide'),
		    'options'    => array(
				'pos'  => 'POS',
				'jne'  => 'JNE',
				'tiki' => 'TIKI',
			)
		);

		return $fields;
	}


	function custom_woocommerce_billing_fields( $fields ) {
		$fields['billing_city']	= create_city_field('billing');
		return $fields;
	}

	function custom_woocommerce_shipping_fields( $fields ) {
		$fields['shipping_city'] = create_city_field('shipping');
		return $fields;
	}

	function woocommerce_myongkir_checkout_field_update_order_meta( $order_id ) {
		global $myongkir;

		if ( $_POST['billing_city'] ) {
			$province_id = $myongkir->convert_to_province_id( $_POST['billing_state'] );
			$city_name = $myongkir->get_city( $_POST['billing_city'], $province_id );
			update_post_meta( $order_id, '_billing_city', esc_attr( $city_name )); // works
		}

		if ( isset($_POST['shipping_city']) ) { // works
			// check if ship to different address is checked
			if( !empty($_POST['shipping_city']) && $_POST['ship_to_different_address'] == 1 ){
				$province_id = $myongkir->convert_to_province_id( $_POST['shipping_state'] );
				$city_name = $myongkir->get_city( $_POST['shipping_city'], $province_id );
			} else {
				$province_id = $myongkir->convert_to_province_id( $_POST['billing_state'] );
				$city_name = $myongkir->get_city( $_POST['billing_city'], $province_id );
			}
			update_post_meta( $order_id, '_shipping_city', esc_attr($city_name));
		}

		if ( !isset($_POST['shipping_city']) ) {
			$province_id = $myongkir->convert_to_province_id( $_POST['billing_state'] );
			$city_name = $myongkir->get_city( $_POST['billing_city'], $province_id );

			update_post_meta( $order_id, '_shipping_city', esc_attr( $city_name ));
		}

	}

	function create_city_field( $type ) {
		global $current_user;

		$field = array(
			'type' 			=> 'select',
			'label' 		=> 'City',
			'placeholder' 	=> 'City',
			'required' 		=> true,
			'class' 		=> array('form-row-wide', 'update_totals_on_change'),
			'clear' 		=> false,
			'options'		=> array(
				'' => __( 'Select an option', 'woocommerce' )
			)
		);

		if( is_user_logged_in() )
		{
			$user_id = $current_user->data->ID;
			$meta_key = ( $type == 'billing' ) ? 'billing_city' :  'shipping_city' ;
			$index_city =  get_user_meta($user_id, $meta_key, true);
			array_push($field['class'], $meta_key . '_' . $index_city);
		}

		return $field;
	}

	function woocommerce_myongkir_my_account_my_address_formatted_address( $data, $customer_id ) {
		global $myongkir;

		// echo 'fooom';
		$province_id = $myongkir->convert_to_province_id( $data['state'] );

		$data['city'] = $myongkir->get_city($data['city'], $province_id);
		return $data;
	}

	function get_cities_js( $hook ) {
		global $post;

		if ( $post->post_content == '[woocommerce_my_account]' || $post->post_content == '[woocommerce_checkout]' ) {
			if( is_user_logged_in() ) {
				global $current_user;

				$user_id = $current_user->data->ID;
				$_SESSION['billing_city'] = get_user_meta($user_id, 'billing_city', true);
				$_SESSION['shipping_city'] = get_user_meta($user_id, 'shipping_city', true);
			}

			$is_permitted_pages =
				( $post->post_content == '[woocommerce_my_account]' || $post->post_content == '[woocommerce_checkout]' );

			$settings = array(
				'ajax_url'  		 => admin_url( 'admin-ajax.php' ),
				'nonce' 			 => wp_create_nonce( constant('MYONGKIR_NONCE') ),
				'is_permitted_pages' => $is_permitted_pages,
				'billing_city' 		 => isset( $_SESSION['billing_city'] ) ? $_SESSION['billing_city'] : null,
				'shipping_city' 	 => isset( $_SESSION['shipping_city'] ) ? $_SESSION['shipping_city'] : null
			);

			wp_enqueue_script( 'ajax-script', plugins_url( 'assets/js/myongkir.js', __FILE__ ), array('jquery') );
			wp_localize_script( 'ajax-script', 'myongkirAjax', $settings );

	    }

	}

	function get_cities_cb() {
		$nonce = $_GET['nonce'];

		// if don't have nonce, set error
		if ( !wp_verify_nonce($nonce, constant('MYONGKIR_NONCE')) ) die($nonce);

		if( get_option('woocommerce_rajaongkir_api_key') == null || get_option('woocommerce_rajaongkir_api_key') == '' ) {
		    echo json_encode( array('Please set RajaOngkir API Key First') );

			die();
		} else {
			$state_id = $_GET['state'];
			global $myongkir;

			$cities = $myongkir->get_cities( $state_id );

		    echo json_encode( $cities );
			die(); // this is required to return a proper result
		}
	}


}
