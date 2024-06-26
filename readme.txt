=== F4 Salutation Fields for WooCommerce ===
Contributors: faktorvier
Donate link: https://www.faktorvier.ch/donate/
Tags: woocommerce, checkout, address, salutation, field, billing, shipping, fields, shop, ecommerce, order, account
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.0.19
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Adds salutation fields to the WooCommerce billing and shipping address.

== Description ==

[F4 Salutation Fields for WooCommerce](https://www.f4dev.ch) adds a dropdown menu (more field types coming soon) to select the address salutation to the billing and/or shipping address.
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

= 1.0.19 =
* Support WooCommerce 8.7
* Support WordPress 6.5

= 1.0.18 =
* Support WooCommerce 8.1
* Support WordPress 6.3

= 1.0.17 =
* Add support for WC "High-Performance Order Storage" feature
* Add support for WC "New product editor" feature
* Support WooCommerce 7.8
* Support WordPress 6.2

= 1.0.16 =
* Support WooCommerce 7.1
* Support WordPress 6.1

= 1.0.15 =
* Update www.f4dev.ch links
* Support WooCommerce 6.8

= 1.0.14 =
* Fix error in Germanized for Woocommerce preview
* Support WooCommerce 6.7

= 1.0.13 =
* Support WooCommerce 6.5
* Support WordPress 6.0

= 1.0.12 =
* Support WordPress 5.9

= 1.0.11 =
* Support WooCommerce 5.5
* Support WordPress 5.8

= 1.0.10 =
* Support WordPress 5.7

= 1.0.9 =
* Support WooCommerce 5.0

= 1.0.8 =
* Support WooCommerce 4.8
* Support WordPress 5.6

= 1.0.7 =
* Save guest checkout fields in session

= 1.0.6 =
* Support WooCommerce 4.4
* Support WordPress 5.5

= 1.0.5 =
* Update translations

= 1.0.4 =
* Support WooCommerce 4.0
* Support WordPress 5.4

= 1.0.3 =
* Fix privacy export and erase

= 1.0.2 =
* Add donation link
* Rename plugin according to the new naming conventions

= 1.0.1 =
* Fix formatted output

= 1.0.0 =
* Initial stable release
