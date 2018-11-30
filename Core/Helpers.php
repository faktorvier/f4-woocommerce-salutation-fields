<?php

namespace F4\WCSF\Core;

/**
 * Core Helpers
 *
 * Helpers for the Core module
 *
 * @since 1.0.0
 * @package F4\WCSF\Core
 */
class Helpers {
	/**
	 * Get plugin infos
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @param string $info_name The info name to show
	 * @return string The requested plugin info
	 */
	public static function get_plugin_info($info_name) {
		if(!function_exists('get_plugins')) {
			require_once(ABSPATH . 'wp-admin/includes/plugin.php');
		}

		$info_value = null;
		$plugin_infos = get_plugin_data(F4_WCSF_PLUGIN_FILE_PATH);

		if(isset($plugin_infos[$info_name])) {
			$info_value = $plugin_infos[$info_name];
		}

		return $info_value;
	}
}

?>
