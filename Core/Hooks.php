<?php

namespace F4\WCSF\Core;

/**
 * Core Hooks
 *
 * Hooks for the Core module
 *
 * @since 1.0.0
 * @package F4\WCSF\Core
 */
class Hooks {
	/**
	 * @var array $settings All the module settings
	 */
	protected static $settings = array(

	);

	/**
	 * Initialize the hooks
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function init() {
		add_action('plugins_loaded', __NAMESPACE__ . '\\Hooks::core_loaded');
		add_action('init', __NAMESPACE__ . '\\Hooks::load_textdomain');
	}

	/**
	 * Fires once the core module is loaded
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function core_loaded() {
		do_action('F4/WCSF/Core/set_constants');
		do_action('F4/WCSF/Core/loaded');

		// Backend
		add_filter('plugin_action_links_' . F4_WCSF_BASENAME, __NAMESPACE__ . '\\Hooks::add_settings_link_to_plugin_list');

		self::load_settings();
	}

	/**
	 * Load module settings
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function load_settings() {
		self::$settings = apply_filters('F4/WCSPE/load_settings', array(

		));
	}

	/**
	 * Load plugin textdomain
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function load_textdomain() {
		$locale = apply_filters('plugin_locale', get_locale(), F4_WCSF_TD);
		load_plugin_textdomain('f4-wc-salutation-fields', false, plugin_basename(F4_WCSF_PATH . 'Core/Lang') . '/');
	}

	/**
	 * Add settings link to plugin list
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_settings_link_to_plugin_list($links) {
		$links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=account') . '">' . __('Settings') . '</a>';

		return $links;
	}
}

?>
