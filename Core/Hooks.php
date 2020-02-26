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
	 * @var array $options All salutation options (key value pairs)
	 * @var array $settings All the module settings
	 */
	protected static $options = null;
	protected static $settings = array();
	protected static $default_settings = array(
		'billing_field_enabled' => 'required',
		'shipping_field_enabled' => 'required',
		'field_type' => 'select',
		'option_values' => array('', 'mr', 'mrs')
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

		// Load settings
		add_action('init', __NAMESPACE__ . '\\Hooks::load_textdomain');
		add_action('init', __NAMESPACE__ . '\\Hooks::load_settings', 11);

		// Checkout and account fields
		add_filter('woocommerce_billing_fields', __NAMESPACE__ . '\\Hooks::add_address_fields', 10, 2);
		add_filter('woocommerce_shipping_fields', __NAMESPACE__ . '\\Hooks::add_address_fields', 10, 2);
		add_filter('woocommerce_my_account_my_address_formatted_address', __NAMESPACE__ . '\\Hooks::add_field_to_formatted_my_account_address', 10, 3);

		// Formatted address
		add_filter('woocommerce_order_formatted_billing_address', __NAMESPACE__ . '\\Hooks::add_field_to_formatted_address', 10, 2);
		add_filter('woocommerce_order_formatted_shipping_address', __NAMESPACE__ . '\\Hooks::add_field_to_formatted_address', 10, 2);
		add_filter('woocommerce_localisation_address_formats', __NAMESPACE__ . '\\Hooks::append_field_to_localisation_address_formats', 10);
		add_filter('woocommerce_formatted_address_replacements', __NAMESPACE__ . '\\Hooks::replace_field_in_formatted_address', 10, 2);

		// Backend
		add_filter('woocommerce_get_settings_account', __NAMESPACE__ . '\\Hooks::add_settings_fields');
		add_filter('woocommerce_customer_meta_fields', __NAMESPACE__ . '\\Hooks::add_customer_meta_fields');
		add_filter('woocommerce_admin_billing_fields', __NAMESPACE__ . '\\Hooks::add_admin_order_fields');
		add_filter('woocommerce_admin_shipping_fields', __NAMESPACE__ . '\\Hooks::add_admin_order_fields');
		add_filter('plugin_action_links_' . F4_WCSF_BASENAME, __NAMESPACE__ . '\\Hooks::add_settings_link_to_plugin_list');

		// Privacy
		add_filter('woocommerce_privacy_export_customer_personal_data_props', __NAMESPACE__ . '\\Hooks::privacy_customer_personal_data_props', 10, 2);
		add_filter('woocommerce_privacy_export_customer_personal_data_prop_value', __NAMESPACE__ . '\\Hooks::privacy_export_customer_personal_data_prop_value', 10, 3);
		add_filter('woocommerce_privacy_erase_customer_personal_data_props', __NAMESPACE__ . '\\Hooks::privacy_customer_personal_data_props', 10, 2);
		add_filter('woocommerce_privacy_erase_customer_personal_data_prop', __NAMESPACE__ . '\\Hooks::privacy_erase_customer_personal_data_prop', 10, 3);
		add_action('woocommerce_privacy_remove_order_personal_data_meta', __NAMESPACE__ . '\\Hooks::privacy_remove_order_personal_data_meta');
	}

	/**
	 * Load plugin textdomain
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function load_textdomain() {
		$locale = apply_filters('plugin_locale', get_locale(), 'f4-wc-salutation-fields');
		load_plugin_textdomain('f4-wc-salutation-fields', false, plugin_basename(F4_WCSF_PATH . 'Core/Lang') . '/');
	}

	/**
	 * Get options
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @return array An associative array with the salutation options, key/value pairs
	 */
	public static function get_options() {
		if(!self::$options) {
			$options = array();

			foreach(self::$settings['option_values'] as $value) {
				switch($value) {
					case '':
						$options[$value] = __('Select');
						break;

					case 'mr':
						$options[$value] = __('Mr.', 'f4-wc-salutation-fields');
						break;

					case 'mrs':
						$options[$value] = __('Mrs.', 'f4-wc-salutation-fields');
						break;
				}
			}

			self::$options = apply_filters('F4/WCSF/get_salutation_options', $options, self::$settings);
		}

		return self::$options;
	}

	/**
	 * Get option label
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 * @param string $key The option key
	 * @return string The translated label for the requested value
	 */
	public static function get_option_label($key) {
		$options = self::get_options();
		return !empty($key) && isset($options[$key]) ? $options[$key] : '';
	}

	/**
	 * Load module settings
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function load_settings() {
		self::$settings = apply_filters(
			'F4/WCSF/load_settings',
			array(
				'billing_field_enabled' => get_option('woocommerce_enable_billing_field_salutation', self::$default_settings['billing_field_enabled']),
				'shipping_field_enabled' => get_option('woocommerce_enable_shipping_field_salutation', self::$default_settings['shipping_field_enabled']),
				'field_type' => get_option('woocommerce_salutation_field_type', self::$default_settings['field_type']),
				'option_values' => get_option('woocommerce_salutation_options', self::$default_settings['option_values'])
			)
		);
	}

	/**
	 * Add fields to edit address forms
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_address_fields($address_fields, $country) {
		$address_type = doing_filter('woocommerce_billing_fields') ? 'billing' : 'shipping';

		if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
			$name_field_priority = \F4\WCSF\Core\Helpers::get_field_priority(
				$address_fields,
				array(
					$address_type . '_first_name',
					$address_type . '_last_name'
				)
			);

			$address_fields[$address_type . '_salutation'] = apply_filters(
				'F4/WCSF/address_' . $address_type . '_field_salutation',
				array(
					'label' => __('Salutation', 'f4-wc-salutation-fields'),
					'required' => self::$settings[$address_type . '_field_enabled'] === 'required',
					'type' => self::$settings['field_type'],
					'options' => self::get_options(),
					'class' => array('form-row-wide'),
					'autocomplete' => 'salutation',
					'priority' => $name_field_priority - 1
				)
			);
		}

		return $address_fields;
	}

	/**
	 * Add fields to edit address dashboard
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_field_to_formatted_my_account_address($address, $customer_id, $address_type) {
		if(self::$settings[$address_type . '_field_enabled'] !== 'hidden' && is_array($address)) {
			$address['salutation'] = self::get_option_label(get_user_meta($customer_id, $address_type . '_salutation', true));
		}

		return $address;
	}

	/**
	 * Add fields to formatted addresses
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_field_to_formatted_address($address, $order) {
		$address_type = doing_filter('woocommerce_order_formatted_billing_address') ? 'billing' : 'shipping';

		if(self::$settings[$address_type . '_field_enabled'] !== 'hidden' && is_array($address)) {
			$address['salutation'] = self::get_option_label(get_post_meta($order->get_id(), '_' . $address_type . '_salutation', true));
		}

		return $address;
	}

	/**
	 * Add fields to localisation address formats
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function append_field_to_localisation_address_formats($formats) {
		if(self::$settings['billing_field_enabled'] !== 'hidden' || self::$settings['shipping_field_enabled'] !== 'hidden') {
			foreach($formats as $country => &$format) {
				$format = preg_replace(
					'/\{(name|name_uppercase|first_name|first_name_uppercase|last_name|last_name_uppercase)\}/im',
					'{salutation} {$1}',
					$format,
					1
				);
			}
		}

		return $formats;
	}

	/**
	 * Replace fields in formatted address
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function replace_field_in_formatted_address($replace, $args) {
		if((self::$settings['billing_field_enabled'] !== 'hidden' || self::$settings['shipping_field_enabled'] !== 'hidden')) {
			if(isset($args['salutation'])) {
				$replace['{salutation}'] = $args['salutation'];
				$replace['{salutation_upper}'] = strtoupper($args['salutation']);
			} else {
				$replace['{salutation_upper}'] = $replace['{salutation}'] = '';
			}
		}

		return $replace;
	}

	/**
	 * Add settings fields to the woocommerce settings page
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_settings_fields($settings) {
		// Section start
		$fields_settings = array(
			array(
				'title' => __('Salutation fields', 'f4-wc-salutation-fields'),
				'type' => 'title',
				'id' => 'salutation-fields'
			)
		);

		// Field typ
		$fields_settings[] = array(
			'title' => __('Type', 'woocommerce'),
			'desc' => '',
			'id' => 'woocommerce_salutation_field_type',
			'type' => 'select',
			'default' => self::$default_settings['field_type'],
			'css' => 'min-width:300px;',
			'desc_tip' =>  true,
			'options' => array(
				'select' => __('Select', 'woocommerce')
			)
		);

		// Billing salutation
		foreach(array('billing', 'shipping') as $address_type) {
			$fields_settings[] = array(
				'title' => $address_type === 'billing' ? __('Billing address', 'woocommerce') : __('Shipping address', 'woocommerce'),
				'desc' => '',
				'id' => 'woocommerce_enable_' . $address_type . '_field_salutation',
				'type' => 'select',
				'default' => self::$default_settings[$address_type . '_field_enabled'],
				'css' => 'min-width:300px;',
				'desc_tip' =>  true,
				'options' => array(
					'hidden' => __('Hidden', 'woocommerce'),
					'optional' => __('Optional', 'woocommerce'),
					'required' => __('Required', 'woocommerce')
				)
			);
		}

		// Section end
		$fields_settings[] = array(
			'type' => 'sectionend',
			'id' => 'salutation-fields'
		);

		$fields_settings = apply_filters('F4/WCSF/settings_fields', $fields_settings);

		// Insert after registration options
		$insert_at_position = null;

		foreach($settings as $index => $setting) {
			if($setting['type'] === 'sectionend' && $setting['id'] === 'account_registration_options') {
				$insert_at_position = $index;
				break;
			}
		}

		array_splice($settings, $insert_at_position + 1, 0, $fields_settings);

		return $settings;
	}

	/**
	 * Add fields to backend user edit page
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_customer_meta_fields($fields) {
		foreach(array('billing', 'shipping') as $address_type) {
			if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
				$fields[$address_type]['fields'] = \F4\WCSF\Core\Helpers::insert_before_key(
					$fields[$address_type]['fields'],
					array(
						$address_type . '_first_name',
						$address_type . '_last_name'
					),
					array(
						$address_type . '_salutation' => apply_filters(
							'F4/WCSF/customer_meta_field_' . $address_type . '_salutation',
							array(
								'label' => __('Salutation', 'f4-wc-salutation-fields'),
								'description' => '',
								'type' => self::$settings['field_type'],
								'options' => self::get_options()
							)
						)
					)
				);
			}
		}

		return $fields;
	}

	/**
	 * Add fields to backend order addresses
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function add_admin_order_fields($fields) {
		$address_type = doing_filter('woocommerce_admin_billing_fields') ? 'billing' : 'shipping';

		if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
			$fields = \F4\WCSF\Core\Helpers::insert_before_key(
				$fields,
				array(
					'first_name',
					'last_name'
				),
				array(
					'salutation' => apply_filters(
						'F4/WCSF/admin_field_' . $address_type . '_salutation',
						array(
							'label' => __('Salutation', 'f4-wc-salutation-fields'),
							'type' => self::$settings['field_type'],
							'wrapper_class' => 'form-field-wide',
							'show' => false,
							'options' => self::get_options()
						)
					)
				)
			);
		}

		return $fields;
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

	/**
	 * Add fields to the privacy customer data props
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function privacy_customer_personal_data_props($props, $customer) {
		foreach(array('billing', 'shipping') as $address_type) {
			if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
				if($address_type === 'billing') {
					$prop_label = __('Billing Salutation', 'f4-wc-salutation-fields');
				} else {
					$prop_label = __('Shipping Salutation', 'f4-wc-salutation-fields');
				}

				$props = \F4\WCSF\Core\Helpers::insert_before_key(
					$props,
					array(
						$address_type . '_first_name',
						$address_type . '_last_name'
					),
					array(
						$address_type . '_salutation' => $prop_label
					)
				);
			}
		}

		return $props;
	}

	/**
	 * Get privacy customer data props values
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function privacy_export_customer_personal_data_prop_value($value, $prop, $customer) {
		if($prop === 'billing_salutation') {
			$value = self::get_option_label($customer->get_meta('billing_salutation'));
		} elseif($prop === 'shipping_salutation') {
			$value = self::get_option_label($customer->get_meta('shipping_salutation'));
		}

		return $value;
	}

	/**
	 * Remove privacy customer data props values
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 */
	public static function privacy_erase_customer_personal_data_prop($erased, $prop, $customer) {
		if($prop === 'billing_salutation') {
			$customer->delete_meta_data('billing_salutation');
		} elseif($prop === 'shipping_salutation') {
			$customer->delete_meta_data('shipping_salutation');
		}

		return $erased;
	}

	/**
	 * Remove privacy order data meta
	 *
	 * @since 1.0.3
	 * @access public
	 * @static
	 */
	public static function privacy_remove_order_personal_data_meta($meta) {
		$meta['_billing_salutation'] = 'text';
		$meta['_shipping_salutation'] = 'text';

		return $meta;
	}
}

?>
