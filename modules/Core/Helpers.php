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

	/**
	 * Insert one or more elements before a specific key
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @param array $array The original array
	 * @param string|array $search_key One or more keys to insert the values before
	 * @param array $target_values The associative array to insert
	 * @return array The new array
	 */
	public static function insert_before_key($array, $search_key, $target_values) {
		$array_new = array();
		$already_inserted = false;

		if(!is_array($search_key)) {
			$search_key = array($search_key);
		}

		foreach($array as $array_key => $array_value) {
			if(in_array($array_key, $search_key) && !$already_inserted) {
				foreach($target_values as $target_key => $target_value) {
					$array_new[$target_key] = $target_value;
				}

				$already_inserted = true;
			}

			$array_new[$array_key] = $array_value;
		}

		return $array_new;
	}

	/**
	 * Insert one or more elements before a specific key
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @param array $fields The fields array
	 * @param string|array $search_key One or more keys to get the priority
	 * @return integer The priority
	 */
	public static function get_field_priority($fields, $search_key) {
		$priority = 0;

		if(!is_array($search_key)) {
			$search_key = array($search_key);
		}

		foreach($fields as $name => $field) {
			if(in_array($name, $search_key)) {
				$priority = $field['priority'];
				break;
			}
		}

		return $priority;
	}
}

?>
