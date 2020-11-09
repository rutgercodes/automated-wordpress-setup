<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Settings class
 */
if ( !class_exists( 'Automated_WordPress_Setup_Themes' ) ) {

	class Automated_WordPress_Setup_Themes {
		
		/**
		 * Constructor
		 */
		public function __construct() {		
			// Define default variables
		}
		
		public static function install_theme( $theme ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();

			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Theme_Upgrader($skin);
			$installed = $upgrader->install( $theme );
			
			return array(
				"installed" =>  $installed,
				"result" => $upgrader->result
			);
		}

	}
}

?>