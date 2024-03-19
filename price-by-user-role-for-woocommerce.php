<?php
/**
 * Plugin Name: Product Prices by User Roles for WooCommerce
 * Plugin URI: https://woocommerce.com/products/product-prices-by-user-roles-for-woocommerce/
 * Description: Display WooCommerce products prices by user roles.
 * Version: 1.8.0
 * Author: Tyche Softwares
 * Author URI: https://www.tychesoftwares.com/
 * Text Domain: price-by-user-role-for-woocommerce
 * Domain Path: /langs
 * Copyright: © 2021 Tyche Softwares
 * WC tested up to: 8.5.2
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 * Requires PHP: 5.6
 *
 * @package PriceByUserRole
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
use Automattic\WooCommerce\Utilities\OrderUtil;

// Check if WooCommerce is active.
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true ) && ! ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) ) ) {
	return;
}

if ( 'price-by-user-role-for-woocommerce.php' === basename( __FILE__ ) ) {
	// Check if Pro is active, if so then return.
	if ( in_array( 'price-by-user-role-for-woocommerce-pro/price-by-user-role-for-woocommerce-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) ), true )
		|| ( is_multisite() && array_key_exists( $plugin, get_site_option( 'active_sitewide_plugins', array() ) ) )
	) {
		return;
	}
}

if ( ! class_exists( 'Alg_WC_Price_By_User_Role' ) ) :

	/**
	 * Main Alg_WC_Price_By_User_Role Class
	 *
	 * @class   Alg_WC_Price_By_User_Role
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	final class Alg_WC_Price_By_User_Role {



		/**
		 * Plugin version.
		 *
		 * @var   string
		 * @since 1.0.0
		 */
		public $version = '1.8.0';

		/**
		 * Instance variable
		 *
		 * @var   Alg_WC_Price_By_User_Role The single instance of the class
		 * @since 1.0.0
		 */
		protected static $inst = null;

		/**
		 * Main Alg_WC_Price_By_User_Role Instance
		 *
		 * Ensures only one instance of Alg_WC_Price_By_User_Role is loaded or can be loaded.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @static
		 * @return  Alg_WC_Price_By_User_Role - Main instance
		 */
		public static function instance() {
			if ( is_null( self::$inst ) ) {
				self::$inst = new self();
			}
			return self::$inst;
		}

		/**
		 * Alg_WC_Price_By_User_Role Constructor.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 * @access  public
		 */
		public function __construct() {

			// Set up localisation.
			load_plugin_textdomain( 'price-by-user-role-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

			// Include required files.
			$this->includes();

			// Admin.
			if ( is_admin() ) {
				add_action( 'before_woocommerce_init', array( &$this, 'pbur_lite_custom_order_tables_compatibility' ), 999 );
				add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
				// Settings.
				require_once 'includes/settings/class-alg-wc-price-by-user-role-settings-section.php';
				$this->settings                = array();
				$this->settings['general']     = require_once 'includes/settings/class-alg-wc-price-by-user-role-settings-general.php';
				$this->settings['multipliers'] = require_once 'includes/settings/class-alg-wc-price-by-user-role-settings-multipliers.php';
				$this->settings['per-product'] = require_once 'includes/settings/class-alg-wc-price-by-user-role-settings-per-product.php';
				// Version check.
				if ( get_option( 'alg_wc_price_by_user_role_version', '' ) !== $this->version ) {
					add_action( 'admin_init', array( $this, 'version_updated' ) );
				}
				add_action( 'admin_footer', array( $this, 'ts_admin_notices_scripts' ) );
				add_action( 'admin_init', array( $this, 'ts_reset_tracking_setting' ) );
				add_action( 'pbur_lite_init_tracker_completed', array( __CLASS__, 'init_tracker_completed' ), 10, 2 );
				add_filter( 'pbur_lite_ts_tracker_data', array( 'Pbur_Tracking_Functions', 'pbur_lite_plugin_tracking_data' ), 10, 1 );
			}

		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 * @param   mixed $links Action link.
		 * @return  array
		 */
		public function action_links( $links ) {
			$custom_links   = array();
			$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_price_by_user_role' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
			if ( 'price-by-user-role-for-woocommerce.php' === basename( __FILE__ ) ) {
				$custom_links[] = '<a href="https://woocommerce.com/products/product-prices-by-user-roles-for-woocommerce/">' . __( 'Unlock All', 'price-by-user-role-for-woocommerce' ) . '</a>';
			}
			return array_merge( $custom_links, $links );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function includes() {
			// Functions.
			require_once 'includes/alg-wc-price-by-user-role-functions.php';
			// Core.
			require_once 'includes/class-alg-wc-price-by-user-role-core.php';
			// plugin deactivation survey v2.
			require_once 'includes/component/plugin-deactivation/class-tyche-plugin-deactivation.php';
			new Tyche_Plugin_Deactivation(
				array(
					'plugin_name'       => 'Product Prices by User Roles for WooCommerce',
					'plugin_base'       => 'price-by-user-role-for-woocommerce/price-by-user-role-for-woocommerce.php',
					'script_file'       => plugins_url() . '/price-by-user-role-for-woocommerce/assets/js/plugin-deactivation.js',
					'plugin_short_name' => 'pbur_lite',
					'version'           => $this->version,
					'plugin_locale'     => 'price-by-user-role-for-woocommerce',
				)
			);
			$doc_link = 'https://www.tychesoftwares.com/docs/docs/price-based-on-user-role-for-woocommerce/';
			require_once 'includes/component/plugin-tracking/class-tyche-plugin-tracking.php';
			require_once 'includes/class-pbur-tracking-functions.php';
			new Tyche_Plugin_Tracking(
				array(
					'plugin_name'       => 'Product Prices by User Roles for WooCommerce',
					'plugin_locale'     => 'price-by-user-role-for-woocommerce',
					'plugin_short_name' => 'pbur_lite',
					'version'           => $this->version,
					'blog_link'         => $doc_link,
				)
			);
		}

		/**
		 * Version_updated.
		 *
		 * @version 1.2.0
		 * @since   1.2.0
		 */
		public function version_updated() {
			foreach ( $this->settings as $section ) {
				foreach ( $section->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			update_option( 'alg_wc_price_by_user_role_version', $this->version );
		}

		/**
		 * * This function includes js files required for admin side.
		 * */
		public function ts_admin_notices_scripts() {
			$nonce = wp_create_nonce( 'tracking_notice' );
			wp_enqueue_script(
				'pbur_lite_ts_dismiss_notice',
				plugins_url() . '/price-by-user-role-for-woocommerce/assets/js/tyche-dismiss-tracking-notice.js',
				'',
				$this->version,
				false
			);
			wp_localize_script(
				'pbur_lite_ts_dismiss_notice',
				'pbur_lite_ts_dismiss_notice',
				array(
					'ts_prefix_of_plugin' => 'pbur_lite',
					'ts_admin_url'        => admin_url( 'admin-ajax.php' ),
					'tracking_notice'     => $nonce,
				)
			);
		}

		/**
		 * Function remove query argument when click on allow button on opt banner.
		 */
		public static function ts_reset_tracking_setting() {
			if ( isset( $_GET ['ts_action'] ) && 'reset_tracking' === $_GET ['ts_action'] ) {// phpcs:ignore WordPress.Security.NonceVerification
				Tyche_Plugin_Tracking::reset_tracker_setting( 'pbur_lite' );
				$ts_url = remove_query_arg( 'ts_action' );
				wp_safe_redirect( $ts_url );
			}
		}

		/**
		 * Function located on settings page after tracking completed.
		 */
		public static function init_tracker_completed() {
			header( 'Location: ' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_price_by_user_role' ) );
			exit;
		}

		/**
		 * Add Price by User Role settings tab to WooCommerce settings.
		 *
		 * @version 1.2.0
		 * @param   mixed $settings Setting link.
		 * @since   1.0.0
		 */
		public function add_woocommerce_settings_tab( $settings ) {
			$settings[] = require_once 'includes/settings/class-alg-wc-settings-price-by-user-role.php';
			return $settings;
		}

		/**
		 * Get the plugin url.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_url() {
			return untrailingslashit( plugin_dir_url( __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 * @return  string
		 */
		public function plugin_path() {
			return untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
		/**
		 * Sets the compatibility with Woocommerce HPOS.
		 *
		 * @since 1.5.0
		 */
		public function pbur_lite_custom_order_tables_compatibility() {

			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', 'price-by-user-role-for-woocommerce/price-by-user-role-for-woocommerce.php', true );
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'orders_cache', 'price-by-user-role-for-woocommerce/price-by-user-role-for-woocommerce.php', true );
			}
		}

	}

endif;

if ( ! function_exists( 'alg_wc_price_by_user_role' ) ) {
	/**
	 * Returns the main instance of Alg_WC_Price_By_User_Role to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_Price_By_User_Role
	 */
	function alg_wc_price_by_user_role() {
		return Alg_WC_Price_By_User_Role::instance();
	}
}

alg_wc_price_by_user_role();
