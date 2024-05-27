<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://lumenspei.com/
 * @since      1.0.0
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/admin
 * @author     Lumen Spei <info@lumenspei.com>
 */
class Deliveryfrom_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
        global $pagenow;
        global $typenow;
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/deliveryfrom-admin.css', array(), /*$this->version*/time(), 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
        global $pagenow;
        global $typenow;
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/deliveryfrom-admin.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-datepicker'), /*$this->version*/time(), false );
	}

    //Setup inaccessible uploads folder
    public function deliveryfrom_setup_uploads_folder(){
        $upload_dir      = trailingslashit( WP_CONTENT_DIR ) . 'uploads/deliveryfrom';

		$files = array(
			array(
				'base'    => $upload_dir,
				'file'    => 'index.html',
				'content' => '',
			),
			array(
				'base'    => $upload_dir,
				'file'    => '.htaccess',
				'content' => 'deny from all',
			),
		);

		foreach ( $files as $file ) {
			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				$file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'wb' ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
				if ( $file_handle ) {
					fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
					fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
				}
			}
		}
    }

    public function deliveryfrom_add_settings_tab($tabs){
        $tabs['deliveryfrom'] = __('DeliveryFrom',' deliveryfrom');
        return $tabs;
    }

    public function deliveryfrom_meta_box(){
        add_meta_box( 'deliveryfrom_meta_box', __('Label', 'deliveryfrom'), array($this, 'deliveryfrom_meta_box_content'), 'shop_order', 'side', 'default' );
        add_meta_box( 'deliveryfrom_meta_box', __('Label', 'deliveryfrom'), array($this, 'deliveryfrom_meta_box_content'), 'woocommerce_page_wc-orders', 'side', 'default' );
    }

    public function deliveryfrom_meta_box_content($order_or_post){

        if ( is_a( $order_or_post, \WP_Post::class ) ) {
			$order = wc_get_order( $order_or_post );
		} else {
			$order = $order_or_post;
		}

        $order_id = $order->get_id();

        if(in_array($order->get_status(), array('failed', 'cancelled'))){
            return;
        }

        $methods = $this->delivreyfrom_get_enabled_methods();

        ?>
        <div class="deliveryfrom_actions single-order">
        <?php
            if(empty($order->get_meta('_deliveryfrom_label_id', true))){
                $this->deliveryfrom_generate_print_buttons($order_id);
            }else{
                $this->deliveryfrom_generate_print_buttons_existing($order_id);
            }
        ?>
        </div>
        <?php
    }

    public function deliveryfrom_settings_output_sections($sections){
        global $current_section;
        $tab_id = 'deliveryfrom';

        $sections = array(
            '' => __( 'General', 'deliveryfrom' ),
        );

        $methods = $this->delivreyfrom_get_enabled_methods();
        foreach($methods as $method){
            $sections['deliveryfrom_' . $method['ID']] = $method['name'];
        }

        $sections = apply_filters('deliveryfrom_settings_sections', $sections);

        echo '<ul class="subsubsub">';

        $array_keys = array_keys( $sections );

        foreach ( $sections as $id => $label ) {
            echo '<li><a href="' . admin_url( 'admin.php?page=wc-settings&tab=' . $tab_id . '&section=' . sanitize_title( $id ) ) . '" class="' . ( $current_section == $id ? 'current' : '' ) . '">' . $label . '</a> | </li>';
        }

        echo '<li><a href="https://deliveryfrom.shop" target="_blank">' . __('Support', 'deliveryfrom') . '</a> | </li>';
        echo '<li><a href="https://deliveryfrom.shop" target="_blank">' . __('Email us', 'deliveryfrom') . '</a></li>';

        echo '</ul><br class="clear" />';
    }

    private function deliveryfrom_get_settings() {
        global $current_section;
        $instance = '';

        if(empty($current_section)){
            $current_section = 'general';
        }

        $method = $this->get_method_for_section($current_section);
        if(is_array($method) && isset($method['ID']) && isset($method['service']) ){
            $current_section = $method['service'];
            $instance = $method['ID'];

            if(!class_exists($current_section)){
                /* translators: url to admin plugins */
                $message = sprintf(
                    __( 'Module for this sections is not installed or enabled, please check your <a href="%s">plugins</a>', 'deliveryfrom' ),
                    admin_url('plugins.php')
                );
                
                printf( '<div class="error"><p>%s</p></div>', $message );
            }
        }

        $settings = apply_filters('deliveryfrom_settings_fields_' . $current_section, array(), $instance);

        return $settings;
    }

    public function deliveryfrom_settings_output_general_section($settings, $instance = ''){
        $settings = array(

            array(
                'title'     => __( 'General settings', 'deliveryfrom' ),
                'type'      => 'title',
                'id'        => 'general_title'
            ),

            'deliveryfrom_google_api_key' => array(
                'title'      => __( 'Google Maps API key', 'deliveryfrom' ),
                'type'       => 'text',
                'desc'       => __( 'Enter Google Maps API key to use maps with compatible pickup point delivery methods', 'deliveryfrom' ),
                'desc_tip'   => true,
                'field_name' => 'deliveryfrom_google_api_key',
                'id'         => 'deliveryfrom_google_api_key',
            ),
            
            'deliveryfrom_quickprint' => array(
                'name' => __( "Quick print", 'deliveryfrom' ),
                'type' => 'checkbox',
                'id'   => 'deliveryfrom_quickprint',
                'desc' => __( "Skip form to check order details and just generate the label", 'deliveryfrom' ),
            ),
            
            'deliveryfrom_auto_complete_orders' => array(
                'name' => __( 'Automatically set order status to complete on label print', 'deliveryfrom' ),
                'type' => 'checkbox',
                'id'   => 'deliveryfrom_auto_complete_orders'
            ),

            'deliveryfrom_cod_methods' => array(
                'name' => __( 'COD Payment Gateways', 'deliveryfrom' ),
				'type' =>   'multiselect',
                'options' => $this->deliveryfrom_get_enabled_payment_methods(),
				'id'   => 'deliveryfrom_cod_methods',
                'class' => 'wc-enhanced-select',
                'desc' => __('Select Payment Gateways which will be send as cash on delivery methods to services', 'deliveryfrom'),
                'desc_tip' => true
			),
        );
        $settings = apply_filters('deliveryfrom_settings_output_general_section', $settings);

        $settings[] = array(
            'type'      => 'sectionend',
        );

        return $settings;
    }

    public function deliveryfrom_settings_render(){
        global $current_section;
        
        $settings = $this->deliveryfrom_get_settings();
        $wc_admin_settings = new WC_Admin_Settings;
        $wc_admin_settings->output_fields( $settings );

        if($current_section == 'general'){

            $available_services = array();
            $available_services = apply_filters('deliveryfrom_available_services', $available_services); //Accepts pairs of class name for value and label for user display
    
            $methods = $this->delivreyfrom_get_enabled_methods();
    
            include __DIR__ . '/partials/deliveryfrom-admin-services.php';
        }
    }

    public function deliveryfrom_display_buttons($order){
        $order_id = $order->get_id();

        if(in_array($order->get_status(), array('failed', 'cancelled'))){
            return;
        }

        $methods = $this->delivreyfrom_get_enabled_methods();
        
        if(empty($order->get_meta('_deliveryfrom_label_id', true))){
            $this->deliveryfrom_generate_print_buttons($order_id);
        }else{
            $this->deliveryfrom_generate_print_buttons_existing($order_id);
        }
    }

    private function deliveryfrom_generate_print_buttons($order_id){

        $order = wc_get_order($order_id);
        $methods = $this->delivreyfrom_get_enabled_methods();

        if(!$order){
            return;
        }
        
        if(empty($order->get_meta('_deliveryfrom_label_id', true))):
            foreach($methods as $method):
                $style = apply_filters('deliveryfrom_action_print_style_' . $method['service'], array('bg' => '#fff', 'icon' => '', 'color' => '#000', 'country' => ''), $method['ID']);
                if(class_exists($method['service'])):
                ?>
                    <a class="button deliveryfrom_wc_action deliveryfrom_print" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>;" href="#" title="<?php printf(__('Print using %s', 'deliveryfrom'), esc_attr($method['name'])); ?>" data-order-id="<?php echo esc_attr($order_id); ?>" data-method="<?php echo esc_attr($method['service']); ?>" data-instance="<?php echo esc_attr($method['ID']); ?>">
                        <img class="deliveryfrom_action_icon" src="<?php echo esc_attr($style['icon']); ?>">
                        <?php if(isset($style['country']) && !empty($style['country'])): ?>
                            <img class="deliveryfrom_country_icon" src="<?php echo esc_attr(DELIVERYFROM_URI . 'admin/images/' . $style['country'] . '.png'); ?>">
                        <?php endif; ?>
                    </a>
                <?php
                endif;
            endforeach;
        endif;
    }

    private function deliveryfrom_generate_print_buttons_existing($order_id){

        $order = wc_get_order($order_id);
        $methods = $this->delivreyfrom_get_enabled_methods();
        
        if( !$order ){
            return;
        }

        $service = $order->get_meta('_deliveryfrom_method', true);
        $instance = $order->get_meta('_deliveryfrom_instance', true);
        $style = apply_filters('deliveryfrom_action_print_style_' . $service, array('bg' => '#fff', 'icon' => '', 'color' => '#000', 'country' => ''), $instance);
        ?>
            <a target="_blank" class="button deliveryfrom_wc_action" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="<?php echo esc_url(admin_url('post.php?action=deliveryfrom_viewlabel&post=' . $order_id)); ?>" title="<?php esc_html_e('Print existing label', 'deliveryfrom'); ?>" data-order-id="<?php echo esc_attr($order_id); ?>">
                <span class="dashicons dashicons-printer"></span>
            </a>

            <?php 
            $tracking_link = apply_filters('deliveryfrom_tracking_url_' . $service, false, $order_id, $instance);
            if($tracking_link):
            ?>
            <a target="_blank" class="button deliveryfrom_wc_action" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="<?php echo esc_url($tracking_link); ?>" title="<?php _e('Open tracking link', 'deliveryfrom'); ?>">
                <span class="dashicons dashicons-location-alt"></span>
            </a>
            <?php endif; ?>
            <a class="button deliveryfrom_wc_action deliveryfrom_cancel_label" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="#" title="<?php _e('Cancel label', 'deliveryfrom'); ?>" data-order-id="<?php echo esc_attr($order_id); ?>">
                <span class="dashicons dashicons-no"></span>
            </a>
        <?php
    }

    private function deliveryfrom_generate_print_buttons_ajax(){

        $methods = $this->delivreyfrom_get_enabled_methods();
        
        foreach($methods as $method):
            $style = apply_filters('deliveryfrom_action_print_style_' . $method['service'], array('bg' => '#fff', 'icon' => '', 'color' => '#000', 'country' => ''), $method['ID']);
        ?>
            <a class="button deliveryfrom_wc_action deliveryfrom_print" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>;" href="#" title="<?php printf(__('Print using %s', 'deliveryfrom'), esc_attr($method['name'])); ?>" data-order-id="{{order_id}}" data-method="<?php echo esc_attr($method['service']); ?>" data-instance="<?php echo esc_attr($method['ID']); ?>">
                <img class="deliveryfrom_action_icon" src="<?php echo esc_attr($style['icon']); ?>">
                <?php if(isset($style['country']) && !empty($style['country'])): ?>
                    <img class="deliveryfrom_country_icon" src="<?php echo esc_attr(DELIVERYFROM_URI . 'admin/images/' . $style['country'] . '.png'); ?>">
                <?php endif; ?>
            </a>
        <?php
        endforeach;
    }

    private function deliveryfrom_generate_print_buttons_existing_ajax($service, $instance, $order_id){

        $style = apply_filters('deliveryfrom_action_print_style_' . $service, array('bg' => '#fff', 'icon' => '', 'color' => '#000', 'country' => ''), $instance);
        ?>
            <a target="_blank" class="button deliveryfrom_wc_action" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="<?php echo esc_url(admin_url('post.php?action=deliveryfrom_viewlabel&post=')) . '{{order_id}}'; ?>" title="<?php _e('Print existing label', 'deliveryfrom'); ?>" data-order-id="{{order_id}}">
                <span class="dashicons dashicons-printer"></span>
            </a>
            <?php 
            $tracking_link = apply_filters('deliveryfrom_tracking_url_' . $service, false, $order_id, $instance);
            if($tracking_link):
            ?>
            <a target="_blank" class="button deliveryfrom_wc_action" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="<?php echo esc_url($tracking_link); ?>" title="<?php _e('Open tracking link', 'deliveryfrom'); ?>">
                <span class="dashicons dashicons-location-alt"></span>
            </a>
            <?php endif; ?>
            <a class="button deliveryfrom_wc_action deliveryfrom_cancel_label" style="background-color: <?php echo esc_attr($style['bg']); ?>; border-color: <?php echo esc_attr($style['bg']); ?>; color: <?php echo esc_attr($style['color']); ?>;" href="#" title="<?php _e('Cancel label', 'deliveryfrom'); ?>" data-order-id="{{order_id}}">
                <span class="dashicons dashicons-no"></span>
            </a>
        <?php
    }

    public function deliveryfrom_show_label($order_id){

        $order = wc_get_order($order_id);

        if(!$order){
            return;
        }

        $filename = $order->get_meta('_deliveryfrom_file', true);
        if(!empty($filename)){
			
            $file = trailingslashit( WP_CONTENT_DIR ) . 'uploads/deliveryfrom/'.$filename;
            $pdf = file_get_contents($file);
            header('Content-type: application/pdf');
            die($pdf);
		}
    }

    public function deliveryfrom_settings_save() {
        $section = '';

        // Sanitize GET parameter
        if (isset($_GET['section']) && !empty($_GET['section'])) {
            $section = sanitize_text_field($_GET['section']);
        }

        if ($section == '') {
            // Sanitize and validate POST parameters
            $settings = $this->deliveryfrom_get_settings();
            woocommerce_update_options($settings);

            $services = isset($_POST['deliveryfrom_services']) ? array_map('sanitize_text_field', $_POST['deliveryfrom_services']) : array();
            $services_labels = isset($_POST['deliveryfrom_services_labels']) ? array_map('sanitize_text_field', $_POST['deliveryfrom_services_labels']) : array();

            $this->save_enabled_services($services, $services_labels);
        } else {
            // Further sanitize and validate data for sections
            $method = $this->get_method_for_section($section);

            if (is_array($method) && isset($method['ID']) && isset($method['service'])) {
                $section = sanitize_text_field($method['service']);
                $instance = intval($method['ID']);
            }

            // Apply filters and update options
            $settings = apply_filters('deliveryfrom_settings_fields_' . $section, array(), $instance);
            woocommerce_update_options($settings);

            // Optionally, trigger action hook
            // do_action('deliveryfrom_settings_save_' . $section, $instance);
        }
    }


    private function save_enabled_services($services, $labels){

        global $wpdb;

        if(!$this->maybe_create_table("{$wpdb->prefix}wcdf_services", "CREATE TABLE {$wpdb->prefix}wcdf_services (`ID` INT NOT NULL AUTO_INCREMENT , `service` VARCHAR(30) NOT NULL , `name` VARCHAR(256) NOT NULL , PRIMARY KEY (`ID`))")){
            return;
        }

        $saving_ids = array_keys($services);

        $existing_ids = $wpdb->get_results("SELECT ID FROM `{$wpdb->prefix}wcdf_services`;", ARRAY_A);

        if(is_array($existing_ids) && sizeof($existing_ids) > 0){
            $existing_ids = array_column($existing_ids, 'ID');
        }else{
            $existing_ids = array();
        }

        //INSERT NEW INFO
        foreach($services as $key => $service){
            if(in_array($key, $existing_ids)){
                $wpdb->update(
                    "{$wpdb->prefix}wcdf_services",
                    array(
                        'service' => $service,
                        'name' => $labels[$key]
                    ),
                    array(
                        'ID' => $key,
                    )
                );
            }else{
                $wpdb->insert(
                    "{$wpdb->prefix}wcdf_services",
                    array(
                        'ID' => $key,
                        'service' => $service,
                        'name' => $labels[$key]
                    )
                );
            }
        }

        //REMOVE REMOVED
        if(is_array($saving_ids)){
            $save = implode(', ', $saving_ids);

            if(sizeof($saving_ids) > 0){
                $to_remove = $wpdb->get_results("SELECT ID FROM `{$wpdb->prefix}wcdf_services` WHERE ID NOT IN ({$save});", ARRAY_A);
            }else{
                $to_remove = $wpdb->get_results("SELECT ID FROM `{$wpdb->prefix}wcdf_services`;", ARRAY_A);
            }
            
            foreach($to_remove as $remove){
                $wpdb->query("DELETE FROM `{$wpdb->options}` WHERE option_name LIKE 'deliveryfrom_{$remove['ID']}%';");
            }

            if(sizeof($saving_ids) > 0){
                $wpdb->query("DELETE FROM `{$wpdb->prefix}wcdf_services` WHERE ID NOT IN ({$save});");
            }else{
                $wpdb->query("DELETE FROM `{$wpdb->prefix}wcdf_services`;");
            }
            
        }
        
    }

    private function maybe_create_table( $table_name, $create_ddl ) {
        global $wpdb;
    
        $query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) );
    
        if ( $wpdb->get_var( $query ) === $table_name ) {
            return true;
        }
    
        // Didn't find it, so try to create it.
        $wpdb->query( $create_ddl );
    
        // We cannot directly tell that whether this succeeded!
        if ( $wpdb->get_var( $query ) === $table_name ) {
            return true;
        }
    
        return false;
    }

    private function delivreyfrom_get_enabled_methods() {
        global $wpdb;

        if(!$this->maybe_create_table("{$wpdb->prefix}wcdf_services", "CREATE TABLE {$wpdb->prefix}wcdf_services (`ID` INT NOT NULL AUTO_INCREMENT , `service` VARCHAR(30) NOT NULL , `name` VARCHAR(256) NOT NULL , PRIMARY KEY (`ID`))")){
            return;
        }
            
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcdf_services ORDER BY ID ASC", ARRAY_A);
    
        return $results;
    }

    private function delivreyfrom_get_method($id) {
        global $wpdb;
    
        $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wcdf_services WHERE ID = {$id} LIMIT 1" , ARRAY_A);
    
        return $results;
    }

    public function deliveryfrom_add_method(){
        $available_services = array();
        $available_services = apply_filters('deliveryfrom_available_services', $available_services); //Accepts pairs of class name for value and label for user display

        $new_id = $this->deliveryfrom_get_next_method_id();

        include __DIR__ . '/partials/deliveryfrom-admin-new-method.php';

        wp_die();
    }

    private function deliveryfrom_get_next_method_id(){
        global $wpdb;

        $new_id = $wpdb->get_var( "SELECT ID FROM `{$wpdb->prefix}wcdf_services` ORDER BY ID DESC LIMIT 1;" );

        return intval($new_id) + 1;
    }

    private function get_method_for_section($section){
        global $wpdb;

        $prefix = 'deliveryfrom_';
        $id = '';

        if (substr($section, 0, strlen($prefix)) == $prefix) {
            $id = substr($section, strlen($prefix));
        }

        if(empty($id)){
            return '';
        }

        $service = $wpdb->get_var( "SELECT service FROM `{$wpdb->prefix}wcdf_services` WHERE ID = $id LIMIT 1;" );

        return array('ID' => $id, 'service' => $service);

    }

    public function deliveryfrom_handle_print_button(){

        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $post = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : '';
        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : '';
        $instance = isset($_POST['instance']) ? sanitize_text_field($_POST['instance']) : '';
        $order = wc_get_order($post);

        if($action != 'deliveryfrom_print'){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Action is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if( !$order ){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Post type is not order", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($method)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Method is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($instance)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Instance is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        $quickprint = get_option('deliveryfrom_quickprint', false);
        if($quickprint == 'yes'){
            $print = apply_filters('deliveryfrom_quick_print_' . $method, '', $post, $instance);
            //Print errors or save data
            do_action('deliveryfrom_handle_print', $print, $method, $instance);
        }else{
            $default_fields = $this->get_form_default_fields($post);
            $form = apply_filters('deliveryfrom_quick_print_fields_' . $method, $default_fields, $post, $instance);

            do_action('deliveryfrom_return_form_html', $form, $post, $method, $instance );
        }
        wp_die();
    }

    private function deliveryfrom_get_enabled_payment_methods(){
        $methods = array();
        $installed_payment_methods = WC()->payment_gateways()->payment_gateways();
        foreach($installed_payment_methods as $key =>  $method){
            $methods[$key] = $method->title;
        }
        return $methods;
    }

    public function deliveryfrom_return_form_html($form, $order_id, $method, $instance){
        $order = wc_get_order($order_id);

        ob_start();
        ?>
        <div id="dff_form_wrap">
            <form id="df_form_print">
                <table>
                    <?php
                    foreach($form['meta'] as $meta):
                        $this->return_form_input_html($meta, true);
                    endforeach;
                    ?>
                    <?php
                    if(isset($form['service']) && is_array($form['service'])){
                        foreach($form['service'] as $service){
                            $this->return_form_input_html($service);
                        }
                    }
                    ?>
                    <?php
                        if(isset($form['services']) && is_array($form['services']))
                            $this->return_form_input_services_html($form['services']);
                    ?>
                </table>
            </form>
            <div class="df_form_options">
                <button class="button button-primary deliveryfrom_form_print" data-order-id="<?php echo esc_attr($order_id); ?>" data-method="<?php echo esc_attr($method); ?>" data-instance="<?php echo esc_attr($instance); ?>"><?php _e('Print', 'deliveryfrom'); ?></button>
                <button class="button deliveryfrom_form_save"><?php _e('Save', 'deliveryfrom'); ?></button>
                <button class="button df-blockui-close"><?php _e('Close', 'deliveryfrom'); ?></button>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        echo json_encode(
            array(
                'status' => 'form',
                'html' => $html
            )
        );
    }

    private function get_form_default_fields($order_id){
        $order = wc_get_order($order_id);

        $first_name = !empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : $order->get_billing_first_name();
        $last_name = !empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : $order->get_billing_last_name();
        $company = !empty($order->get_shipping_company()) ? $order->get_shipping_company() : $order->get_billing_company();
        $phone = !empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : $order->get_billing_phone();
        $email = $order->get_billing_email();
        $city = !empty($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city();
        $country = !empty($order->get_shipping_country()) ? $order->get_shipping_country() : $order->get_billing_country();
        $address = !empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : $order->get_billing_address_1();
        $postcode = !empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : $order->get_billing_postcode();

        

        $countries_obj   = new WC_Countries();
        $countries   = $countries_obj->__get('countries');

        $fields = array(
            'meta' => array(
                array(
                    'type' => 'text',
                    'field' => '_shipping_first_name',
                    'label' => __('First name', 'deliveryfrom'),
                    'value' => $first_name,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_last_name',
                    'label' => __('Last name', 'deliveryfrom'),
                    'value' => $last_name,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_company',
                    'label' => __('Company', 'deliveryfrom'),
                    'value' => $company,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_address_1',
                    'label' => __('Address', 'deliveryfrom'),
                    'value' => $address,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_city',
                    'label' => __('City', 'deliveryfrom'),
                    'value' => $city,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_postcode',
                    'label' => __('Postcode', 'deliveryfrom'),
                    'value' => $postcode,
                ),
                array(
                    'type' => 'select',
                    'field' => '_shipping_country',
                    'label' => __('Country', 'deliveryfrom'),
                    'options' => $countries,
                    'value' => $country,
                ),
                array(
                    'type' => 'text',
                    'field' => '_shipping_phone',
                    'label' => __('Phone', 'deliveryfrom'),
                    'value' => $phone,
                ),
                array(
                    'type' => 'text',
                    'field' => '_billing_email',
                    'label' => __('Email', 'deliveryfrom'),
                    'value' => $email,
                )
            )
        );

        return $fields;
    }

    private function generate_filename($ext){
        $unique_name = wp_generate_password(11, false, false);
        for ( $i = 0; $i < 1; $i++ ) {

            $unique_name = strtolower( wp_generate_password( 11, false, false ) );
            
            if(file_exists(trailingslashit( WP_CONTENT_DIR ) . 'uploads/deliveryfrom/' . $unique_name . '.' . $ext)){
                $i--;
            }else{
                break;
            }
        }
        return $unique_name;
    }

    //Function to save pdf to file and update post meta
    public function deliveryfrom_handle_print($print, $method, $instance){
        $filename = '';
        $single = false;
        $printed = array();
        $html = '';

        if(is_wp_error($print)){
            echo json_encode(array(
                'status' => 'err',
                'error' => $print->get_error_message(),
                'close' => __('Close', 'deliveryfrom')
            ));
            return;
        }

        if(isset($print['status']) && $print['status'] == 'err'){

            $print['close'] =  __('Close', 'deliveryfrom');
            echo json_encode($print);
            return;
        }

        if(isset($print['pdf']) && !empty($print['pdf'])){
            $filename = $this->save_to_file($print['pdf'], 'pdf');
            $single = true;
        }

        if(isset($print['successful']) && is_array($print['successful']) && sizeof($print['successful']) > 0){
            foreach($print['successful'] as $save){

                $order = wc_get_order($save['order_id']);
                if(!$order){
                    break;
                }

                if(isset($save['pdf']) && !empty($save['pdf'])){
                    $filename = $this->save_to_file($save['pdf'], 'pdf');
                }

                $order->update_meta_data('_deliveryfrom_method', $method);
                $order->update_meta_data('_deliveryfrom_instance', $instance);
                $order->update_meta_data('_deliveryfrom_label_id', $save['label_id']);
                $order->update_meta_data('_deliveryfrom_tracking_number', $save['label_tracking']);
                $order->update_meta_data('_deliveryfrom_file', $filename);

                $printed[] = $save['order_id'];

                if(get_option('deliveryfrom_auto_complete_orders') == 'yes'){
                    $order->update_status('completed', __('Order status was updated automatically by DeliveryFrom plugin', 'deliveryfrom'));
                }

                $order->save();
            }

            if(sizeof($print['successful']) == 1){
                $single = true;
            }

            ob_start();
            $this->deliveryfrom_generate_print_buttons_existing_ajax($method, $instance, $save['order_id']);
            $html = ob_get_contents();
            ob_end_clean();

            if(isset($print['server']) && !empty($print['server'])){

                $args = array(
                    'timeout'     => '2',
                    'redirection' => '10',
                    'httpversion' => '1.1',
                    'blocking'    => true,
                    'headers'     => array(),
                    'cookies'     => array(),
                    'body'        => array(
                        'server' => $print['server'],
                        'type' => 3
                    )
                );
            
                $response = wp_remote_post( 'https://deliveryfrom.shop/license/api/save.php', $args);
                
            }

        }

        if(!isset($print['successful']) || !is_array($print['successful']) || sizeof($print['successful']) == 0){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("No orders were printed", "deliveryfrom"),
                        'close' => __('Close', 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        echo json_encode(array('status' => 'ok', 'single' => $single, 'printed' => $printed, 'url' => admin_url('post.php?action=deliveryfrom_viewlabel&post=' . $save['order_id']), 'buttons' => $html));
    }

    public function deliveryfrom_handle_form_print(){
        
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $post = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : '';
        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : '';
        $instance = isset($_POST['instance']) ? sanitize_text_field($_POST['instance']) : '';
        $form = isset($_POST['form']) ? sanitize_textarea_field(stripslashes($_POST['form'])) : '';
        $order = wc_get_order($post);

        if($action != 'deliveryfrom_form_print'){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Action is not set", "deliveryfrom"),
                        'close' => __("Close", 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        if( !$order ){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Post type is not order", "deliveryfrom"),
                        'close' => __("Close", 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        if(empty($method)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Method is not set", "deliveryfrom"),
                        'close' => __("Close", 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        if(empty($instance)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Instance is not set", "deliveryfrom"),
                        'close' => __("Close", 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        if(empty($form)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("No fields found", "deliveryfrom"),
                        'close' => __("Close", 'deliveryfrom')
                    )
                )
            );
            wp_die();
        }

        $fields = json_decode($form, true);

        $print = apply_filters('deliveryfrom_form_print_' . $method, '', $post, $fields, $instance);
        do_action('deliveryfrom_handle_print', $print, $method, $instance);

        $order = wc_get_order($post);
        if(!$order){
            wp_die();
        }
        
        if( !empty($order->get_meta('_deliveryfrom_tracking_number')) ){
            // Get the WC_DeliveryFrom_Customer_Tracking_Email object
            $email_tracking_number = WC()->mailer()->get_emails()['WC_DeliveryFrom_Customer_Tracking_Email'];

            // Sending the order email tracking notification for an $order_id (order ID)
            $email_tracking_number->trigger( $post );
        }

        wp_die();
    }

    private function save_to_file($content, $ext){
        $filename = $this->generate_filename($ext);
        file_put_contents(trailingslashit( WP_CONTENT_DIR ) . 'uploads/deliveryfrom/'.$filename.'.'.$ext, $content);
        return $filename.'.'.$ext;
    }

    private function return_form_input_html($field, $meta = false){
        if($meta){
            $prefix = 'dfform_meta_';
        }else{
            $prefix = 'dfform_custom_';
        }
        ?>
        <tr>
            <th scope="row" class="deliveryfrom_form_label">
                <label for="<?php echo esc_attr($prefix.$field['field']); ?>"><?php esc_html_e($field['label']); ?></label>
            </th>
            <td class="deliveryfrom_form_input">
                <?php if($field['type'] == 'textarea'): ?>
                    <textarea id="<?php echo esc_attr($prefix.$field['field']); ?>" name="<?php echo esc_attr($prefix.$field['field']); ?>"><?php echo esc_attr($field['value']); ?></textarea>
                <?php elseif($field['type'] == 'date'): ?>
                    <input id="<?php echo esc_attr($prefix.$field['field']); ?>" name="<?php echo esc_attr($prefix.$field['field']); ?>" type="text" class="datepicker" value="<?php echo esc_attr($field['value']); ?>">
                <?php elseif($field['type'] == 'select'): ?>
                    <select id="<?php echo esc_attr($prefix.$field['field']); ?>" name="<?php echo esc_attr($prefix.$field['field']); ?>">
                        <?php foreach($field['options'] as $key => $option): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($key, $field['value'], true); ?>><?php echo esc_attr($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input id="<?php echo esc_attr($prefix.$field['field']); ?>" name="<?php echo esc_attr($prefix.$field['field']); ?>" type="text" value="<?php echo esc_attr($field['value']); ?>">
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    private function return_form_input_services_html($services){
        if(!isset($services['options']) || !is_array($services['options']) || sizeof($services['options']) == 0){
            return;
        }
        ?>
        <tr>
            <th scope="row" class="deliveryfrom_form_label">
                <label for="deliveryfrom_services"><?php _e('Services', 'deliveryfrom'); ?></label>
            </th>
            <td class="deliveryfrom_form_input">
                <select id="deliveryfrom_services" name="dfform_services[]" style="" class="wc-enhanced-select" multiple="" tabindex="-1" aria-hidden="true">
                    <?php foreach($services['options'] as $key => $option): ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected(in_array($key, $services['values']), true); ?>><?php echo esc_attr($option); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <?php
    }

    public function deliveryfrom_add_bulk_actions($actions){
        $methods = $this->delivreyfrom_get_enabled_methods();
        
        foreach($methods as $method){
            $actions['deliveryfrom_bulk_' . $method['ID']] = sprintf(__('Print using %s', 'deliveryfrom'), esc_attr($method['name']));
        } 
        //$bulk_actions['change-to-published'] = __('Change to published', 'txtdomain');
        return $actions;
    }

    public function deliveryfrom_handle_bulk_print() {
        // Sanitize and validate action
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        if ($action !== 'deliveryfrom_bulk_print') {
            $this->json_error(__('Action is not set', 'deliveryfrom'));
        }

        // Sanitize method and extract instance
        $method = isset($_POST['method']) ? sanitize_text_field($_POST['method']) : '';
        $instance = str_replace('deliveryfrom_bulk_', '', $method);
        $instance = sanitize_text_field($instance);

        // Get method details
        $method_details = $this->delivreyfrom_get_method($instance);
        if (is_array($method_details) && !empty($method_details[0]['service'])) {
            $method = sanitize_text_field($method_details[0]['service']);
        } else {
            $method = '';
        }

        if (empty($method)) {
            $this->json_error(__('Method is not set', 'deliveryfrom'));
        }

        // Sanitize and validate orders array
        $orders = isset($_POST['orders']) ? array_map('intval', $_POST['orders']) : array();
        if (empty($orders) || !is_array($orders) || count($orders) < 1) {
            $this->json_error(__('No orders selected', 'deliveryfrom'));
        }

        if (empty($instance)) {
            $this->json_error(__('Instance is not set', 'deliveryfrom'));
        }

        // Apply filter and handle print
        $print = apply_filters('deliveryfrom_bulk_print_' . $method, '', $orders, $instance);
        do_action('deliveryfrom_handle_print', $print, $method, $instance);

        wp_die();
    }

    private function json_error($message) {
        echo json_encode(array(
            'status' => 'err',
            'error' => $message
        ));
        wp_die();
    }


    public function deliveryfrom_cancel_label(){
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $post = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : '';

        $order = wc_get_order($post);
        if(!$order){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Post type is not order", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        $method = $order->get_meta('_deliveryfrom_method', true);
        $instance = $order->get_meta('_deliveryfrom_instance', true);

        if($action != 'deliveryfrom_cancel_label'){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Action is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($method)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Method is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($instance)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Instance is not set", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($order->get_meta('_deliveryfrom_label_id', true))){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("This order doesn't have label ID", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        $removed = apply_filters('deliveryfrom_remove_label_' . $method, false, $post, $instance);
        do_action('deliveryfrom_handle_remove', $removed, $post, $method, $instance);

        wp_die();
    }

    public function deliveryfrom_handle_remove($removed, $post, $method, $instance){

        $html = '';

        $order = wc_get_order($post);
        if(!$order){
            echo json_encode(array(
                'status' => 'err',
                'error' => __("Post type is not order", "deliveryfrom"),
                'close' => __('Close', 'deliveryfrom')
            ));
            return;
        }

        if(is_wp_error($removed)){
            echo json_encode(array(
                'status' => 'err',
                'error' => $removed->get_error_message(),
                'close' => __('Close', 'deliveryfrom')
            ));
            return;
        }

        if(isset($removed['status']) && $removed['status'] == 'err'){

            $removed['close'] =  __('Close', 'deliveryfrom');
            echo json_encode($removed);
            return;
        }

        if(isset($removed['status']) && $removed['status'] == 'ok'){

            $order->delete_meta_data('_deliveryfrom_file');
            $order->delete_meta_data('_deliveryfrom_tracking_number');
            $order->delete_meta_data('_deliveryfrom_label_id');
            $order->delete_meta_data('_deliveryfrom_instance');
            $order->delete_meta_data('_deliveryfrom_method');

            $order->save();

            ob_start();
            $this->deliveryfrom_generate_print_buttons_ajax();
            $html = ob_get_contents();
            ob_end_clean();

        }

        echo json_encode(array('status' => 'ok', 'removed' => esc_js($post), 'buttons' => wp_kses_post($html)));
    }

    public function deliveryfrom_register_emails( $emails ){
        require_once 'class-wc-deliveryfrom-customer-tracking.php';

		$emails['WC_DeliveryFrom_Customer_Tracking_Email'] = new WC_DeliveryFrom_Customer_Tracking_Email();

		return $emails;
    }

    public function deliveryfrom_form_save_order(){
        $action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';
        $post = isset($_POST['post']) ? sanitize_text_field($_POST['post']) : '';
        $form = isset($_POST['form']) ? sanitize_textarea_field(stripslashes($_POST['form'])) : '';

        $order = wc_get_order($post);
        if(!$order){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Post type is not order", "deliveryfrom"),
                        'close' => __("Close", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if($action != 'deliveryfrom_form_save'){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("Action is not set", "deliveryfrom"),
                        'close' => __("Close", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        if(empty($form)){
            print_r(
                json_encode(
                    array(
                        'status' => 'err',
                        'error' => __("No fields found", "deliveryfrom"),
                        'close' => __("Close", "deliveryfrom")
                    )
                )
            );
            wp_die();
        }

        $fields = json_decode($form);

        foreach($fields as $meta_key => $meta_value){

            if(strpos($meta_key, 'dfform_meta_') !== false){
                $meta_key = str_replace('dfform_meta_', '', $meta_key);
                $order->update_meta_data($meta_key, $meta_value);
            }
        }

        $order->save();

        echo json_encode(array('status' => 'ok'));

        wp_die();
    }

    public function deliveryfrom_request(
        $url = '',
        $method = 'GET',
        $headers = array(),
        $body = null,
        $sslverify = true,
        $timeout = 5,
        $redirection = 5,
        $httpversion = '1.0',
        $blocking = true
    ){
        try{
            $response = wp_remote_request(
                $url,
                array(
                    'method' => $method,
                    'headers' => $headers,
                    'body' => $body,
                    'timeout' => $timeout,
                    'redirection' => $redirection,
                    'httpversion' => $httpversion,
                    'sslverify' => $sslverify,
                    'blocking' => $blocking,
                    
                )
            );
            return $response;
        }catch(Exception $e){
            return $e;
        }

    }
}