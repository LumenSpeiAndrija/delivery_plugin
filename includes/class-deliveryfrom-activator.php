<?php

/**
 * Fired during plugin activation
 *
 * @link       https://lumenspei.com/
 * @since      1.0.0
 *
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Deliveryfrom
 * @subpackage Deliveryfrom/includes
 * @author     Lumen Spei <info@lumenspei.com>
 */
class Deliveryfrom_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        if(!get_option('deliveryfrom_cod_methods')){
            update_option('deliveryfrom_cod_methods', array('cod'));
        }
	}

}
