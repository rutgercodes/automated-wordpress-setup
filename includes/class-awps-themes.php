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

		public static function get_theme_info( $url ) {
			
			// Create ZipArchive from temp file to read
			$temp_file = 'tmp_file.zip';
			if (!copy($url, $temp_file)) return [
				'valid' => false,
				'message' => "Can't access file"
			];

			$zip = new ZipArchive();
			if ($zip->open($temp_file, ZIPARCHIVE::CREATE)!==TRUE) {
				unlink($temp_file);
				return  [
					'valid' => false,
					'message' => "Invalid .zip file"
				];
			}

			$dir = trim($zip->getNameIndex(0), '/');
			$dir = explode('/',$dir);
			$dir = $dir[0];

			$theme_info = [];

			if ($zip->open($temp_file)) {
				$style = $zip->getFromName( $dir."/style.css");
				// Get theme info comment from css file
				preg_match('/^\/\*([^*]*?)\*\//', $style, $css_theme_info_comment);
				// Regex match data from theme info comment
				preg_match_all('/\n*([^:]+):\s(.+)\n*/', $css_theme_info_comment[1], $css_theme_info_matches);
				// Combine to key value array
				$css_keys_clean = array_map( array(self, 'string_to_safe_slug'), $css_theme_info_matches[1] );
				$css_values_clean = array_map('trim', $css_theme_info_matches[2]);
				$css_theme_data = array_combine( $css_keys_clean, $css_values_clean );
			}

			$css_theme_data['slug'] = $dir;

			unlink($temp_file);

			return [
				'valid' => true,
				'data' => $css_theme_data
			];

		}

		public static function is_theme_installed( $dir ) {


			$theme_directory = WP_CONTENT_DIR . "/themes/$dir";
			return is_dir($theme_directory);

		}
		
		public static function install_theme( $theme ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			wp_cache_flush();

			$skin = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Theme_Upgrader($skin);
			$installed = $upgrader->install( $theme );

			if ( is_wp_error( $installed ) ) {
				return [
					"installed" => false,
					"result" => $installed::get_error_messages()
				];
			}
			
			return [
				"installed" =>  $installed,
				"result" => $upgrader->result
			];
		}

		private function string_to_safe_slug( $string ) {

			// Trim whitespaces
			$slug = trim($string);
			// Remove unsafe characters
			$slug = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $slug);
			// Whitespaces to underscores
			$slug = preg_replace("/\s/", '_', $slug);
			// Lowercase
			$slug = strtolower( $slug );

			return $slug;

		}

	}
}

?>