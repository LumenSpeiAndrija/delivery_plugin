<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://lumenspei.com/
 * @since      1.0.0
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/public
 * @author     Lumen Spei <info@lumenspei.com>
 */
class Deliveryfrom_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Deliveryfrom_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Deliveryfrom_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/deliveryfrom-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Deliveryfrom_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Deliveryfrom_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/deliveryfrom-public.js', array( 'jquery' ), $this->version, false );

        if(is_checkout()){
            $pickup_methods = apply_filters('deliveryfrom_pickup_methods', array('glsce_parcelshop'));
            $google_key = get_option('deliveryfrom_google_api_key');
            wp_enqueue_script( $this->plugin_name . '_pickup_checkout', plugins_url('/deliveryfrom/public/js/checkout.js'), array( 'jquery', 'wc-checkout', 'selectWoo' ), time(), false );

            if(!empty($google_key)){
                wp_enqueue_script( $this->plugin_name . '_pickup_google_maps', 'https://maps.googleapis.com/maps/api/js?key='.$google_key.'&libraries=&v=weekly&channel=2', array($this->plugin_name . '_pickup_checkout'), null, true);
                wp_enqueue_script($this->plugin_name . '_pickup_markerclusterer', 'https://unpkg.com/@googlemaps/markerclusterer@2.0.8/dist/index.min.js', array($this->plugin_name . '_pickup_google_maps'), null, true);
            }
            

            $js = 'var deliveryfrompickup = '. json_encode($pickup_methods) . '; var deliveryfromimages = "'. plugins_url('/deliveryfrom/public/images/').'";';
            wp_add_inline_script( $this->plugin_name . '_pickup_checkout', $js, 'before' );
        }
        
	}

    public function deliveryfrom_pickup_order_meta( $order_id ){

        $order = wc_get_order($order_id);
        if(!$order){
            return;
        }
        $pickup_methods = apply_filters('deliveryfrom_pickup_methods', array('glsce_parcelshop'));

        foreach($pickup_methods as $pickup_method){
            if(isset($_POST['shipping_method']) && strpos($_POST['shipping_method'][0], $pickup_method) !== false ) {
                if( isset( $_POST['deliveryfrom-pickuppoint'] )) {
                    $order->update_meta_data('_deliveryfrom_pickuppoint', sanitize_text_field( $_POST['deliveryfrom-pickuppoint'] ) );
                    $order->save();
                }
            }
        }
    }

    public function deliveryfrom_pickup_order_checkout_process($fields){

        $pickup_methods = apply_filters('deliveryfrom_pickup_methods', array('glsce_parcelshop'));

        foreach($pickup_methods as $pickup_method){
            if(isset($_POST['shipping_method']) && strpos($_POST['shipping_method'][0], $pickup_method) !== false ) {
                if( isset( $_POST['deliveryfrom-pickuppoint'] ) && empty( $_POST['deliveryfrom-pickuppoint'] ) )
                    wc_add_notice( __( 'Drop off point is required for selected shipping method', 'deliveryfrom' ), "error" );
            }
        }
    }

    public function deliveryfrom_add_pickup_dropdown($fields){

        $fields['billing']['deliveryfrom-pickuppoint'] = array(
            'label'     => __('Select pickup point', 'deliveryfrom'),
            'required'  => false,
            'class'     => array('form-row-wide'),
            'clear'     => true,
            'type'      => 'select',
            'options'   => array('' => __('Not selected', 'deliveryfrom')),

        );
        
        return $fields;
    }

    public function deliveryfrom_update_pickuppoint_checkout(){

        $country = sanitize_text_field($_POST['country']);
        $shipping = sanitize_text_field($_POST['shipping']);

        $pickup_methods = apply_filters('deliveryfrom_pickup_methods', array('glsce_parcelshop'));

        //Check if it's a pickup method and find which one
        foreach($pickup_methods as $pickup_method){
            if(isset($shipping) && strpos($shipping, $pickup_method) !== false ) {
                $shipping = $pickup_method;
                break;
            }
        }

        $pickup_points = array(
            'options' => '',
            'markers' => '',
            'zoom' => 6,
            'lat' => 0,
            'lng' => 0,
            'icon' => '',
            'clusterer' => '',
            'textColor' => 'rgba(255,255,255,1)'
        );

        $pickup_points = apply_filters('deliveryfrom_pickuppoints_checkout', $pickup_points , $country, $shipping);
        
        wp_send_json(
            $pickup_points,
            200
        );
        wp_die();
        
    }

}
