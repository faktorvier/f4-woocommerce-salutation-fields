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
		'billing_field_enabled' => 'required',
		'shipping_field_enabled' => 'required',
		'field_type' => 'select',
		'option_values' => array('', 'mr', 'mrs')
	);

	protected static $options = null;

	/**
	 * Initialize the hooks
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
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
		add_action('init', __NAMESPACE__ . '\\Hooks::load_settings', 99);

		// Checkout and account fields
		add_filter('woocommerce_checkout_fields', __NAMESPACE__ . '\\Hooks::add_checkout_fields', 99);
		add_filter('woocommerce_billing_fields', __NAMESPACE__ . '\\Hooks::add_address_fields', 99, 2);
		add_filter('woocommerce_shipping_fields', __NAMESPACE__ . '\\Hooks::add_address_fields', 99, 2);

		// Formatted address
		add_filter('woocommerce_order_formatted_billing_address', __NAMESPACE__ . '\\Hooks::add_field_to_formatted_address', 99, 2);
		add_filter('woocommerce_order_formatted_shipping_address', __NAMESPACE__ . '\\Hooks::add_field_to_formatted_address', 99, 2);
		add_filter('woocommerce_localisation_address_formats', __NAMESPACE__ . '\\Hooks::append_field_to_localisation_address_formats', 99);
		add_filter('woocommerce_formatted_address_replacements', __NAMESPACE__ . '\\Hooks::replace_field_in_formatted_address', 99, 2);

		// Backend
		add_filter('woocommerce_get_settings_account', __NAMESPACE__ . '\\Hooks::add_settings_fields', 99);
		add_filter('woocommerce_customer_meta_fields', __NAMESPACE__ . '\\Hooks::add_customer_meta_fields', 99);
		add_filter('woocommerce_admin_billing_fields', __NAMESPACE__ . '\\Hooks::add_admin_order_fields', 99);
		add_filter('woocommerce_admin_shipping_fields', __NAMESPACE__ . '\\Hooks::add_admin_order_fields', 99);
		add_filter('plugin_action_links_' . F4_WCSF_BASENAME, __NAMESPACE__ . '\\Hooks::add_settings_link_to_plugin_list');
	}

	/**
	 * Load plugin textdomain
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
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
	 *
	 * @done
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
	 *
	 * @done
	 */
	public static function get_option_label($option) {
		$options = self::get_options();
		$label = !empty($option) && isset($options[$option]) ? $options[$option] : '';

		return $label;
	}

	/**
	 * Load module settings
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @todo: settings dynamisch
	 */
	public static function load_settings() {
		$settings = array(
			'billing_field_enabled' => get_option('woocommerce_enable_billing_field_salutation', self::$settings['billing_field_enabled']),
			'shipping_field_enabled' => get_option('woocommerce_enable_shipping_field_salutation', self::$settings['shipping_field_enabled']),
			'field_type' => get_option('woocommerce_salutation_field_type', self::$settings['field_type']),
			'option_values' => get_option('woocommerce_salutation_options', self::$settings['option_values'])
		);

		self::$settings = apply_filters('F4/WCSF/load_settings', $settings);
	}

	/**
	 * Add fields to the checkout address forms
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @todo: select = select2
	 */
	public static function add_checkout_fields($fields) {
		foreach(array('billing', 'shipping') as $address_type) {
			if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
				$name_field_priority = \F4\WCSF\Core\Helpers::get_field_priority(
					$fields[$address_type],
					array(
						$address_type . '_first_name',
						$address_type . '_last_name'
					)
				);

				$fields[$address_type][$address_type . '_salutation'] = apply_filters(
					'F4/WCSF/checkout_' . $address_type . '_field_salutation',
					array(
						'label' => __('Salutation', 'f4-wc-salutation-fields'),
						'required' => self::$settings[$address_type . '_field_enabled'] === 'required',
						'type' => 'select',
						'options' => self::get_options(),
						'class' => array('form-row-wide'),
						'autocomplete' => 'salutation',
						'priority' => $name_field_priority - 1
					)
				);
			}
		}

		return $fields;
	}

	/**
	 * Add fields to edit address form
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @todo: select = select2
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
					'type' => 'select',
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
	 * Add field to formatted address
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
	 */
	public static function add_field_to_formatted_address($address, $order) {
		$address_type = doing_filter('woocommerce_order_formatted_billing_address') ? 'billing' : 'shipping';

		if(self::$settings[$address_type . '_field_enabled'] !== 'hidden') {
			$address['salutation'] = self::get_option_label(get_post_meta($order->get_id(), '_' . $address_type . '_salutation', true));
		}

		return $address;
	}

	/**
	 * Add field to localisation address formats
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
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
	 *
	 * @done
	 */
	public static function replace_field_in_formatted_address($replace, $args) {
		if(self::$settings['billing_field_enabled'] !== 'hidden' || self::$settings['shipping_field_enabled'] !== 'hidden') {
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
	 *
	 * @todo: settings enabled adden
	 * @todo: settings field type adden
	 * @todo prio 2: settings salutations adden
	 */
	public static function add_settings_fields($settings) {
		return $settings;
	}

	/**
	 * Add fields to backend user edit page
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
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
							'F4/WCSPE/customer_meta_field_' . $address_type . '_salutation',
							array(
								'label' => __('Salutation', 'f4-wc-salutation-fields'),
								'description' => '',
								'type' => 'select',
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
	 * Add fields to backend order address
	 *
	 * @since 1.0.0
	 * @access public
	 * @static
	 *
	 * @done
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
						'F4/WCSPE/admin_field_' . $address_type . '_salutation',
						array(
							'label' => __('Salutation', 'f4-wc-salutation-fields'),
							'type' => 'select',
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
	 *
	 * @done
	 */
	public static function add_settings_link_to_plugin_list($links) {
		$links[] = '<a href="' . admin_url('admin.php?page=wc-settings&tab=account') . '">' . __('Settings') . '</a>';
		return $links;
	}
}

?>
