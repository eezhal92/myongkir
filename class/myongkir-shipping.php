<?php
/**
 *
 * @author eezhal
 * @package myongkir/class
 */

 
if ( ! defined( 'ABSPATH' ) ) { 
    exit; 
}

require_once 'request.php';
require_once 'helper-functions.php';

class MyOngkir_Shipping {
	const SERVER = 'http://rajaongkir.com/api/starter';	
	private $api_key = '';
	private static $request = null;	
	protected static $instance;

	public function __construct() {		
		// $this->api_key = $api_key;
		
		if( self::$request === null ) {					
			self::$request = new Request(array(
	 			'server' => self::SERVER
	 		));
		}
				
		return self::$request;
	}

	public static function get_instance() {
		if(!static::$instance) {
			static ::$instance = new self;
		}

		return static::$instance;
	}
	

	public function set_api_key($api_key) {
		$this->api_key = $api_key;
	}

	/**
	* get_cities function.
	*
	* @access public
	* @param integer $from
	* @param integer $to
	* @param float $weight
	* @return array
	*/
	public function get_costs( $from, $to, $weight ) {
		$result = self::$request->post('/cost', array(
			'key' => $this->api_key,
			'origin' => $from, 
			'destination' => $to, // on going 
			'weight' => $weight * 1000,
		));

		try {			
			$costs = object_to_array( $result->rajaongkir->results );

			// echo "<pre>";
			// print_r($result);
			// echo "</pre>";

			$new_costs = object_to_array( $costs );
			return $new_costs;
		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}	
	}

	/**
	* get_cities function.
	*
	* @access public
	* @param integer $woocommerce_default_country
	* @return array
	*/
	public function get_cities( $woocommerce_default_country ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		$result = self::$request->get('/city', array(
			'key' => $this->api_key,
			'province' => $this->convert_to_province_id( $woocommerce_default_country )
		));

		try {			
			$cities = object_to_array( $result->rajaongkir->results );

			
			$simple_cities = array();

			foreach ($cities as $city) {
				$simple_cities[$city['city_id']] = $city['city_name'];
			}
			// print_r( $simple_cities );

			return $simple_cities;

		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}

	public function get_city( $rajaongkir_city_id, $rajaongkir_province_id ) {
		// NOTE: check if province id have prefix ID:xx for the first install
		$result = self::$request->get('/city', array(
			'key' => $this->api_key,
			'province' => $rajaongkir_province_id,
			'id' => $rajaongkir_city_id
		));

		try {			
			$city = object_to_array( $result->rajaongkir->results ); 
		
			$city = $city['city_name'];

			return $city;
			
		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	* get_provinces function.
	*
	* @access public
	* @param array
	* @return array
	*/
	public function get_provinces() {
		// NOTE: check if province id have prefix ID:xx for the first install
		$result = self::$request->get('/province', array(
			'key' => $this->api_key,			
		));

		try {			
			$provinces = object_to_array( $result->rajaongkir->results );

			$simple_provice = array();

			foreach ($provinces as $province) {
				$simple_provice[$province['province_id']] = $province['province'];
			}

			// print_r( $simple_provice );

			return $simple_provice;

		} catch ( Exception $e ) {
			var_dump( 'ERROR Catched! Message: ' . $e->getMessage() );
			return false;
		}
	}	

	/**
	 * Convert woocommerce base_state to rajaongkir province_id
	 *
	 * @access public
	 * @param  string $woocommerce_default_country
	 * @return integer 
	 */
	public function convert_to_province_id( $woocommerce_base_state ) {
		$provinces = array(
			'AC' => 21,
			'SU' => 34,
			'SB' => 32,
			'RI' => 26,
			'KR' => 17,
			'JA' => 8,
			'SS' => 33,
			'BB' => 2,
			'BE' => 4,
			'LA' => 18,
			'JK' => 6,
			'JB' => 9,
			'BT' => 3,
			'JT' => 10,
			'JI' => 11,
			'YO' => 5,
			'BA' => 1,
			'NB' => 22,
			'NT' => 23,
			'KB' => 12,
			'KT' => 14,
			'KI' => 15,
			'KS' => 13,
			'KU' => 16,
			'SA' => 31,
			'ST' => 29,
			'SG' => 30,
			'SR' => 27,
			'SN' => 28,
			'GO' => 7,
			'MA' => 19,
			'MU' => 20,
			'PA' => 24,
			'PB' => 25
		);
		
		if( array_key_exists( $woocommerce_base_state, $provinces ) ) {
			return $provinces[$woocommerce_base_state];
		}		
	} 

}