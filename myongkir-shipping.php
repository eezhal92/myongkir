<?php
/**
 * @package myongkir
 * @category Core
 * @author eezhal
 */

class MyOngkir_Shipping_Method extends WC_Shipping_Method {
	/**
	 * Constructor for shipping class
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
		$this->id                 = 'myongkir_shipping'; // Id for your shipping method. Should be uunique.
		$this->method_title       = __( 'MyOngkir Method' );  // Title shown in admin
		$this->method_description = __( 'WooCommerce add-on for indonesian local shipping.'); // Description shown in admin																	

		$this->init();
	}

	/**
	 * Init your settings
	 *
	 * @access public
	 * @return void
	 */
	function init() {					
		// Load the settings API
		$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
		$this->init_settings(); // This is part of the settings API. Loads settings you previously init.										

		$this->title = "MyOngkir Shipping Method"; // This can be added as an setting but for this example its forced.							
		$this->enabled = $this->settings['enabled']; // if api key not setted, enabled = false
		$this->api_key = $this->settings['api_key'];

		$this->base_city = $this->settings['base_city'];
							
		global $myongkir;
		// Save settings in admin if you have any defined
		add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
		// handle frontend cost request
		add_action( 'wp_ajax_get_costs', 'calculate_shipping' );						
	}

	function isset_rajaongkir_api() {
		$rajaongkir_api = get_option('woocommerce_rajaongkir_api_key');

		if( $rajaongkir_api == '' || $rajaongkir_api == null) {						
			return false;
		}						

		return true;
	}

	function admin_options() {
	?>
		<h2><?php _e('MyOngkir Shipping','woocommerce'); ?></h2>
		<table class="form-table">
			<?php 
				$rajaongkir_api = get_option('woocommerce_rajaongkir_api_key');

				if( !$this->isset_rajaongkir_api() ) {
			?>
				<div id="message" class="error fade"><p><strong>Please Set RajaOngkir API Key first, on woocommerce general settings.</strong></p></div>
			<?php
				} else {
					 $this->generate_settings_html();
				}
				
			?>
		</table> <?php
	}								

	function init_form_fields() {
		global $woocommerce;
		global $myongkir; 
		// $province_id = $myongkir->convert_to_province_id( $woocommerce->countries->get_base_state() );
		if ( $this->isset_rajaongkir_api() ) {
		    $this->form_fields = array(
		    	'enabled' => array(
				    'title' => 'Enable/Disable',
				    'type' => 'checkbox', // pilih salah satu						    
				    'default' => 'no',						    
				    'label' => 'Enable this shipping method' // checkbox only
				    
					),				    
				'base_city' => array(
			         'title' => __( 'Your Base City', 'woocommerce' ),
			         'type' => 'select',					         
			         'id' => 'woocommerce_myongkir_base_city', // generate woocommerce_myongkir_base_city
			         'class' => 'chosen_select',
			         'description' => __( 'This is your store location, used for origin package. Require api key is setted, before showing available city.', 'woocommerce' ),
			         'options' => $myongkir->get_cities( $woocommerce->countries->get_base_state() )
			    	)
		    );
		}
	}			

	/**
	 * calculate_shipping function.
	 *
	 * @access public
	 * @param mixed $package
	 * @return void
	 */
	public function calculate_shipping( $package ) {					
		global $woocommerce;
		global $myongkir;
		
		$current_shipping_city = $woocommerce->customer->get_shipping_city();
		$current_cart_weight = $woocommerce->cart->cart_contents_weight;

		// var_dump($current_shipping_city);
		$current_currency = get_woocommerce_currency();
		
		$couriers = $this->get_available_shippings( $current_shipping_city, $current_cart_weight );										
		if( $couriers ) {
			foreach( $couriers as $courier ) {						
				foreach ( $courier['costs'] as $item ) {							
					foreach ( $item['cost'] as $cost ) {								
						$this->add_rate(
							array(
								'id' => $this->id . "_" . $courier['name'] . "_" . $item['service'] ,
								'label' => $courier['name'] . " " . $item['service'],
								'cost' => $myongkir->convert_currency( $current_currency, $cost['value'] ),
								'calc_tax' => 'per_item'
							)
						);
					}														
				}
			}
		}					
		
	}				

	private function get_available_shippings( $shipping_city, $cart_weight ) {
		if( $origin_city = $this->settings['base_city'] ) {
			global $myongkir;															

			return $myongkir->get_costs( $origin_city, $shipping_city, $cart_weight );										
		}
		echo $this->settings['base_city'];					
		// break shipping calculation if origin city not (base_city) set
		echo "DEBUG: Break shipping calculation if origin city not (base_city) set <br>";
		var_dump($this->settings['base_city']);					

	}
}


?>