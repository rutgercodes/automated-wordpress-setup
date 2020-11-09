<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Settings class
 */
if ( !class_exists( 'Automated_WordPress_Setup_Plugins' ) ) {

	class Automated_WordPress_Setup_Plugins {
		
		/**
		 * Constructor
		 */
		public function __construct() {		
			// Define default variables
		}

		public static function is_plugin_installed( $slug ) {

			$pluginDir = WP_PLUGIN_DIR . '/' . $slug;
			return is_dir($pluginDir);

		}
				
		public static function install_plugin( $download_link ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();

			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader($skin);
			$installed = $upgrader->install( $download_link );
			
			return $installed;
		}
		
		public static function upgrade_plugin( $api ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();

			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader($skin);
			$upgraded = $upgrader->upgrade( $api->download_link );
			
			return $upgraded;
		}
		
		public static function activate_plugins( $slug ) {

			$errors = false;

			$plugin_files =  self::get_plugin_files( $slug );
			foreach( $plugin_files as $plugin ) {
				$activated = activate_plugin( $plugin );
				if( !is_null($activated) ) {
					$errors = true;
				}
			}

			return !$errors;
			
		}

		private static function get_plugin_files( $plugin_name ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';

			$files = array();

			$plugins = get_plugins("/".$plugin_name);
			foreach( $plugins as $plugin_file => $plugin_info ) {
				$files[] = trailingslashit( $plugin_name ) . $plugin_file;
			}
			return $files;
		}

	}
}

?>