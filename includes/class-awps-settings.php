<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


/**
 * Settings class
 */
if ( !class_exists( 'Automated_WordPress_Setup_Settings' ) ) {

	class Automated_WordPress_Setup_Settings {
		
		/**
		 * Constructor
		 */
		public function __construct() {		
			// Define default variables
			$this->settings_page = 'automated-setup';
			
			// Load the hooks
			// add_action( 'admin_init', array( $this, 'load_admin_hooks' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
			add_action( 'admin_menu', array( $this, 'load_admin_page' ) );
		}
		
		/**
		 * Load the admin hooks
		 */
		public function load_admin_hooks() {

            // add_filter( 'woocommerce_get_sections_shipping', array( $this, 'add_shipping_section' ), 10, 1 );
            // add_filter( 'woocommerce_get_settings_shipping', array( $this, 'add_shipping_settings' ), 10, 2 );
		}
		
		/**
		 * Load the admin scripts
		 */
		public function load_admin_scripts() {
			
			if ( is_admin() ){ // for Admin Dashboard Only

				// Embed the Script on our Plugin's Option Page Only
				if ( isset($_GET['page']) && $_GET['page'] == $this->settings_page ) {
					
					// Scripts
					wp_register_script( Automated_WordPress_Setup::$plugin_prefix . 'admin-script', Automated_WordPress_Setup::$plugin_url . 'assets/js/admin.js', array( 'jquery-ui-core', 'jquery-ui-tabs' ), Automated_WordPress_Setup::$plugin_version, true );
					wp_localize_script( Automated_WordPress_Setup::$plugin_prefix . 'admin-script', 'plugin_prefix', Automated_WordPress_Setup::$plugin_prefix );
					wp_enqueue_script( Automated_WordPress_Setup::$plugin_prefix . 'admin-script' );

					// Styles
					wp_enqueue_style( Automated_WordPress_Setup::$plugin_prefix . 'admin-style', Automated_WordPress_Setup::$plugin_url . 'assets/css/admin.css', array(), Automated_WordPress_Setup::$plugin_version );
				
				}
			}

		}
		
		/**
		 * Load the admin page
		 */
		public function load_admin_page() {
			add_submenu_page( 'tools.php', 'Automated setup', 'Automated setup', 'administrator', $this->settings_page, array( $this, 'automated_setup_page'), 11 );
		}
		
		public function automated_setup_page() {

			$defaults = parse_ini_file( Automated_WordPress_Setup::$plugin_path . 'defaults.ini' );

			echo '<div class="wrap">';
			echo '<h1>Install plugins & theme</h1>';
			echo '<table class="form-table" role="presentation"><tbody>';
			echo '<h2>Themes</h2>';
			echo '<tr>
			<th scope="row"><label for="theme">Main theme</label></th>
			<td><input name="theme" type="text" id="theme" value="'. $defaults['theme']['main'] .'" class="regular-text">';
			echo '<p class="description" id="theme-description">Enter the url to a .zip file.</p>';
			echo '</td></tr>';
			echo '<tr>
			<th scope="row"><label for="child-theme">Child theme</label></th>
			<td><input name="child-theme" type="text" id="child-theme" value="'. $defaults['theme']['child'] .'" class="regular-text">';
			echo '<p class="description" id="child-theme-description">Enter the url to a .zip file.</p>';	
			echo '</td></tr>';
			echo '<tr>
			<th scope="row"><label for="child-theme">Overwrite child theme name</label></th>
			<td><input name="child-theme-name" type="text" id="child-theme-name" class="regular-text">';
			echo '<p class="description" id="child-theme-description">Enter name to ovewrite the child theme name. Leave empty to use name from theme file.</p>';	
			echo '</td></tr>';
			echo '</tbody></table>';
			echo '<h2>Plugins</h2>';
			echo '<table class="form-table" role="presentation"><tbody>';
			echo '<tr>
			<th scope="row"><label for="plugins">Plugin list</label></th>
			<td><textarea name="plugins" id="plugins" class="regular-text" rows="10">'.join("&#13", $defaults['plugins']).'</textarea>';
			echo '<p class="description" id="plugins-description">Enter the plugins slugs. One per line.</p>';
			echo '</td></tr>';
			echo '<tr>
			<th scope="row">WooCommerce</th>
			<td> <fieldset><legend class="screen-reader-text"><span>WooCommerce</span></legend><label for="install_woocommerce">';
			echo '<input name="install_woocommerce" type="checkbox" id="install_woocommerce" value="1">
			Install WooCommerce</label>';
			echo '</fieldset></td></tr>';
			echo '<tr class="hide-if-js woocommerce-only">
			<th scope="row"><label for="woocommerce_plugins">WooCommerce Plugins</label></th>
			<td><textarea name="woocommerce_plugins" id="woocommerce_plugins" class="regular-text" rows="10">'.join("&#13", $defaults['woocommerce']).'</textarea>';
			echo '<p class="description" id="woocommerce_plugins-description">Enter the plugins slugs. One per line.</p>';
			echo '</td></tr>';
			echo '</tbody></table>';
			echo '<input type="submit" name="install" id="install" class="button button-primary" value="Install" disabled="disabled">';
			echo '<div id="results"></div>';
			echo '</div>';
		}
		
		private function upgrade_plugin( $api ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();

			$skin = new WP_Ajax_Upgrader_Skin($api);
			$upgrader = new Plugin_Upgrader($skin);
			$upgraded = $upgrader->upgrade( $api->download_link );
			
			return $upgraded;
		}
		
		private function activate_plugins( $slug ) {

			$errors = false;

			$plugin_files =  $this->get_plugin_files( $slug );
			foreach( $plugin_files as $plugin ) {
				$activated = activate_plugin( $plugin );
				if( !is_null($activated) ) {
					$errors = true;
				}
			}

			return !$errors;
			
		}

		private function get_plugin_files( $plugin_name ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';

			$files = array();

			$plugins = get_plugins("/".$slug);
			foreach( $plugins as $plugin_file => $plugin_info ) {
				$files[] = trailingslashit( $slug ) . $plugin_file;
			}
			return $files;
		}

	}
}

?>