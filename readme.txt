=== F4 Salutation Fields for WooCommerce ===
Contributors: faktorvier
Donate link: https://www.faktorvier.ch/donate/
Tags: woocommerce, checkout, address, salutation, field, billing, shipping, fields, shop, ecommerce, order, account
Requires at least: 4.4.0
Tested up to: 5.2
Requires PHP: 5.6
Stable tag: 1.0.1
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds salutation fields to the WooCommerce billing and shipping address.

== Description ==

F4 Salutation Fields for WooCommerce adds a dropdown menu (more field types coming soon) to select the address salutation to the billing and/or shipping address.
The plugin prepends the salutation automatically before the name in every formatted address output. Here are a few more things the plugin does:

* Adds salutation fields to the billing and/or shipping address checkout form
* Adds salutation fields to the edit billing and/or shipping address dashboard and form
* Adds salutation fields to the edit order backend page
* Shows salutation fields in privacy data export
* Erases salutation data if privacy erase is requested
* Shows salutation field in orders (thank you page, email etc.)

Currently only Mr. and Mrs. are shown in the salutation field. If you want to add more or translate this values, see the code snippet below.

= Usage =

This plugin works out-of-the-box. By default, both salutation fields for billing and shipping are enabled and marked as required.
You can change the settings for both fields on the Accounts & Privacy screen in your WooCommerce settings. Both fields you can hide or set to optional/required.

If you want to add more salutations, change the labels or add your own translations, you can use this hook:

	add_filter('F4/WCSF/get_salutation_options', function($options, $settings) {
		// Change label
		$options['mr'] = 'Mister';

		// Add new labels
		$options['dr'] = 'Dr.';
		$options['prof'] = 'Prof.';

		return $options;
	}, 10, 2);

= Features overview =

* Adds salutation fields
* Works without configuration
* Can be configurated for both fields
* Easy to use
* Lightweight and optimized
* 100% free!

= Planned features =

* Full integration into API and REST
* More field types than select
* Easy to configure and add own options

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/f4-woocommerce-salutation-fields` directory, or install the plugin through the WordPress plugins screen directly
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Woocommerce -> Settings -> Accounts & Privacy screen to configure the plugin

== Screenshots ==

1. Field in checkout form
2. Fields on order confirmation page
3. Fields in order confirmation e-mail
4. Fields on the order admin page
5. Fields on the edit address dashboard
6. Fields in edit address form
7. Field configuration in WooCommerce settings

== Changelog ==

= 1.0.1 =
* Fix formatted output

= 1.0.0 =
* Initial stable release
