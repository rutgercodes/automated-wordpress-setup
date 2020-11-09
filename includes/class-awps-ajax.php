<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Settings class
 */
if ( !class_exists( 'Automated_WordPress_Setup_Ajax' ) ) {

	class Automated_WordPress_Setup_Ajax {
		
		/**
		 * Constructor
		 */
		public function __construct() {		
			// Define default variables

			// Load the hooks
			add_action( 'wp_loaded', array( $this, 'load_admin_ajax' ) );
		}
		
		/**
		 * Load the admin ajax funtions
		 */
		public function load_admin_ajax() {
			
			add_action( 'wp_ajax_'.Automated_WordPress_Setup::$plugin_prefix.'install_plugin', array($this, 'install_plugin') );
			add_action( 'wp_ajax_'.Automated_WordPress_Setup::$plugin_prefix.'install_theme', array($this, 'install_theme') );

		}

		public function install_plugin() {

			include_once( 'class-awps-plugins.php' );
			$this->settings = new Automated_WordPress_Setup_Plugins();			

			$slug = $_POST['plugin'];

			if(!$slug) {
				echo json_encode( array(
					"error" => true,
					"message" => "No plugin slug provided"
				));
				wp_die();
			}
			
			// Stop if plugin already exists
			if( Automated_WordPress_Setup_Plugins::is_plugin_installed($slug) ) {
				echo json_encode( array(
					"error" => true,
					"message" => "Plugin ($slug) already installed"
				));
				wp_die(); // this is required to terminate immediately and return a proper response
			}
			
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug' => $slug,
					'fields' => array(
						'short_description' => false,
						'sections' => false,
						'requires' => false,
						'rating' => false,
						'ratings' => false,
						'downloaded' => false,
						'last_updated' => false,
						'added' => false,
						'tags' => false,
						'compatibility' => false,
						'homepage' => false,
						'donate_link' => false,
					),
				)
			);

			$installed = Automated_WordPress_Setup_Plugins::install_plugin( $api->download_link );

			if ($installed !== true) {
				echo json_encode( array(
					"error" => true,
					"message" => "Install process failed ('$slug').",
				));
				wp_die(); // this is required to terminate immediately and return a proper response
			}

			$activated = Automated_WordPress_Setup_Plugins::activate_plugins( $slug );

			if ( !$activated ) {
				echo json_encode( array(
					"error" => false,
					"message" => "Activation failed ($slug). Manually activate the plugin."
				));
				wp_die(); // this is required to terminate immediately and return a proper response
			}

			echo json_encode( array(
				"error" => false,
				"message" => "Plugin ".$api->name." installed & activated",
			));
			wp_die(); // this is required to terminate immediately and return a proper response
		}

		public static function install_theme() {

			include_once( 'class-awps-themes.php' );
			$this->settings = new Automated_WordPress_Setup_Themes();	

			$theme = $_POST['theme'];
			$activate = $_POST['activate'];

			if(!$theme) {
				echo json_encode( array(
					"error" => true,
					"message" => "No theme provided"
				));
				wp_die();
			}

			$installer = Automated_WordPress_Setup_Themes::install_theme( $theme );

			if ($installer['installed'] !== true) {
				echo json_encode( array(
					"error" => true,
					"message" => "Install process failed ('$theme').",
				));
				wp_die(); // this is required to terminate immediately and return a proper response
			}

			$message = "Installed $theme";

			if( $activate && $installer["result"]["destination_name"] ) {
				switch_theme( $installer["result"]["destination_name"] );
				$message = "Installed & activated ".$installer["result"]["destination_name"];
			}
			
			echo json_encode( array(
				"error" => false,
				"message" => "Installed $theme"
			));
			wp_die();

		}
	}
}

?>