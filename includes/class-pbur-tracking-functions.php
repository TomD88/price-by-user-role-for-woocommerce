<?php
/**
 * Custom Order Status for WooCommerce - Data Tracking Functions
 *
 * @version 1.0.0
 * @since   1.5.0
 * @package Custom Order Numbers/Data Tracking
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {

	exit; // Exit if accessed directly.

}

if ( ! class_exists( 'Pbur_Tracking_Functions' ) ) :

	/**
	 * Custom Order Status Data Tracking Functions.
	 */
	class Pbur_Tracking_Functions {
		/**
		 * Construct.
		 *
		 * @since 1.5.0
		 */
		public function __construct() {
		}

		/**
		 * Returns plugin data for tracking.
		 *
		 * @param array $data - Generic data related to WP, WC, Theme, Server and so on.
		 * @return array $data - Plugin data included in the original data received.
		 * @since 1.5.0
		 */
		public static function pbur_lite_plugin_tracking_data( $data ) {
			$plugin_data         = array(
				'ts_meta_data_table_name' => 'ts_tracking_pbur_lite_meta_data',
				'ts_plugin_name'          => 'Product Prices by User Roles for WooCommerce',
				'global_settings'         => self::pbur_get_global_settings(),
				'multipliers_roles'       => self::pbur_get_multipliers_roles(),
			);
			$data['plugin_data'] = $plugin_data;
			return $data;
		}

		/**
		 * Send the global settings for tracking.
		 *
		 * @since 1.5.0
		 */
		public static function pbur_get_global_settings() {
			$global_settings = array(
				'alg_wc_price_by_user_role_enabled' => get_option( 'alg_wc_price_by_user_role_enabled' ),
				'alg_wc_price_by_user_role_for_bots_disabled' => get_option( 'alg_wc_price_by_user_role_for_bots_disabled' ),
				'alg_wc_price_by_user_role_multipliers_enabled' => get_option( 'alg_wc_price_by_user_role_multipliers_enabled' ),
				'alg_wc_price_by_user_role_shipping_enabled' => get_option( 'alg_wc_price_by_user_role_shipping_enabled' ),
				'alg_wc_price_by_user_role_per_product_enabled' => get_option( 'alg_wc_price_by_user_role_per_product_enabled' ),
				'alg_wc_price_by_user_role_per_product_show_roles' => get_option( 'alg_wc_price_by_user_role_per_product_show_roles' ),

			);
			return $global_settings;
		}

		/**
		 * Send the Statuses for which emails are sent(Email-settings).
		 *
		 * @since 1.5.0
		 */
		public static function pbur_get_multipliers_roles() {
			$multipliers_roles = array(
				'alg_wc_price_by_user_role_guest'         => get_option( 'alg_wc_price_by_user_role_guest' ),
				'alg_wc_price_by_user_role_empty_price_guest' => get_option( 'alg_wc_price_by_user_role_empty_price_guest' ),
				'alg_wc_price_by_user_role_administrator' => get_option( 'alg_wc_price_by_user_role_administrator' ),
				'alg_wc_price_by_user_role_empty_price_administrator' => get_option( 'alg_wc_price_by_user_role_empty_price_administrator' ),
				'alg_wc_price_by_user_role_editor'        => get_option( 'alg_wc_price_by_user_role_editor' ),
				'alg_wc_price_by_user_role_empty_price_editor' => get_option( 'alg_wc_price_by_user_role_empty_price_editor' ),
				'alg_wc_price_by_user_role_author'        => get_option( 'alg_wc_price_by_user_role_author' ),
				'alg_wc_price_by_user_role_empty_price_author' => get_option( 'alg_wc_price_by_user_role_empty_price_author' ),
				'alg_wc_price_by_user_role_contributor'   => get_option( 'alg_wc_price_by_user_role_contributor' ),
				'alg_wc_price_by_user_role_empty_price_contributor' => get_option( 'alg_wc_price_by_user_role_empty_price_contributor' ),
				'alg_wc_price_by_user_role_subscriber'    => get_option( 'alg_wc_price_by_user_role_subscriber' ),
				'alg_wc_price_by_user_role_empty_price_subscriber' => get_option( 'alg_wc_price_by_user_role_empty_price_subscriber' ),
				'alg_wc_price_by_user_role_shop_manager'  => get_option( 'alg_wc_price_by_user_role_shop_manager' ),
				'alg_wc_price_by_user_role_seller'        => get_option( 'alg_wc_price_by_user_role_seller' ),
				'alg_wc_price_by_user_role_vendor_staff'  => get_option( 'alg_wc_price_by_user_role_vendor_staff' ),
			);
			return $multipliers_roles;
		}
	}

endif;

$pbur_tracking_functions = new Pbur_Tracking_Functions();
