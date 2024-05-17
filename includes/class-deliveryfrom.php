<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://lumenspei.com/
 * @since      1.0.0
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/includes
 * @author     Lumen Spei <info@lumenspei.com>
 */
class Deliveryfrom {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Deliveryfrom_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;




	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'DELIVERYFROM_VERSION' ) ) {
			$this->version = DELIVERYFROM_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'deliveryfrom';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Deliveryfrom_Loader. Orchestrates the hooks of the plugin.
	 * - Deliveryfrom_i18n. Defines internationalization functionality.
	 * - Deliveryfrom_Admin. Defines all hooks for the admin area.
	 * - Deliveryfrom_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deliveryfrom-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-deliveryfrom-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-deliveryfrom-admin.php';

        /**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-deliveryfrom-update.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-deliveryfrom-public.php';

        //require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/class-gls-ce.php';

        foreach (glob(plugin_dir_path( dirname( __FILE__ ) ) . 'includes/services/*.php') as $filename)
        {
            //var_dump($filename);
            require_once $filename;
        }

		$this->loader = new Deliveryfrom_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Deliveryfrom_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Deliveryfrom_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Deliveryfrom_Admin( $this->get_plugin_name(), $this->get_version() );


        $this->loader->add_filter( 'woocommerce_settings_tabs_array', $plugin_admin, 'deliveryfrom_add_settings_tab', 30, 1 );
        $this->loader->add_filter( 'woocommerce_sections_deliveryfrom', $plugin_admin, 'deliveryfrom_settings_output_sections', 10, 1 );
        $this->loader->add_filter( 'woocommerce_settings_deliveryfrom', $plugin_admin, 'deliveryfrom_settings_render', 10, 1 );
        $this->loader->add_action( 'woocommerce_settings_save_deliveryfrom',  $plugin_admin, 'deliveryfrom_settings_save', 10, 0 );
        $this->loader->add_filter( 'deliveryfrom_settings_fields_general', $plugin_admin, 'deliveryfrom_settings_output_general_section', 10, 1 );

        $this->loader->add_action( 'admin_init',  $plugin_admin, 'deliveryfrom_setup_uploads_folder', 10, 0 );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'post_action_deliveryfrom_viewlabel', $plugin_admin, 'deliveryfrom_show_label', 10, 1 );
        $this->loader->add_filter( 'bulk_actions-edit-shop_order', $plugin_admin, 'deliveryfrom_add_bulk_actions', 500, 1 );
        $this->loader->add_filter( 'bulk_actions-woocommerce_page_wc-orders', $plugin_admin, 'deliveryfrom_add_bulk_actions', 500, 1 );
        
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'deliveryfrom_meta_box', 10, 1 );
        $this->loader->add_action( 'woocommerce_admin_order_actions_end',  $plugin_admin, 'deliveryfrom_display_buttons', 15, 1 );
        $this->loader->add_action( 'woocommerce_email_classes',  $plugin_admin, 'deliveryfrom_register_emails', 90, 1 );
        
        $this->loader->add_action( 'wp_ajax_deliveryfrom_add_method',  $plugin_admin, 'deliveryfrom_add_method', 10, 0 );
        $this->loader->add_action( 'wp_ajax_deliveryfrom_print',  $plugin_admin, 'deliveryfrom_handle_print_button', 10, 0 );
        $this->loader->add_action( 'wp_ajax_deliveryfrom_form_print',  $plugin_admin, 'deliveryfrom_handle_form_print', 10, 0 );
        $this->loader->add_action( 'wp_ajax_deliveryfrom_bulk_print',  $plugin_admin, 'deliveryfrom_handle_bulk_print', 10, 0 );
        $this->loader->add_action( 'wp_ajax_deliveryfrom_cancel_label',  $plugin_admin, 'deliveryfrom_cancel_label', 10, 0 );
        $this->loader->add_action( 'wp_ajax_deliveryfrom_form_save',  $plugin_admin, 'deliveryfrom_form_save_order', 10, 0 );
        
        $this->loader->add_action( 'deliveryfrom_handle_print',  $plugin_admin, 'deliveryfrom_handle_print', 10, 3 );
        $this->loader->add_action( 'deliveryfrom_return_form_html',  $plugin_admin, 'deliveryfrom_return_form_html', 10, 4 );
        $this->loader->add_action( 'deliveryfrom_handle_remove',  $plugin_admin, 'deliveryfrom_handle_remove', 10, 4 );

        

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Deliveryfrom_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'deliveryfrom_pickup_order_meta' );
        $this->loader->add_action( 'woocommerce_checkout_process', $plugin_public, 'deliveryfrom_pickup_order_checkout_process', 10, 1 );
        $this->loader->add_filter( 'woocommerce_checkout_fields', $plugin_public, 'deliveryfrom_add_pickup_dropdown', 10, 1 );

        $this->loader->add_action( 'wp_ajax_deliveryfrom_update_pickuppoints_checkout', $plugin_public, 'deliveryfrom_update_pickuppoint_checkout', 10, 0 );
        $this->loader->add_action( 'wp_ajax_nopriv_deliveryfrom_update_pickuppoints_checkout', $plugin_public, 'deliveryfrom_update_pickuppoint_checkout', 10, 0 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Deliveryfrom_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}