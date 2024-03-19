<?php
/**
 * Price by User Role for WooCommerce - Settings
 *
 * @package PriceByUserRole
 * @version 1.2.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Settings_Price_By_User_Role' ) ) :

	/**
	 * Alg_WC_Settings_Price_By_User_Role Class
	 *
	 * @class   Alg_WC_Settings_Price_By_User_Role
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	class Alg_WC_Settings_Price_By_User_Role extends WC_Settings_Page {
		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id    = 'alg_wc_price_by_user_role';
			$this->label = __( 'Product Prices by User Roles', 'price-by-user-role-for-woocommerce' );
			parent::__construct();
		}

		/**
		 * Get_settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function get_settings() {
			global $current_section;
			return apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() );
		}

		/**
		 * Maybe_reset_settings.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function maybe_reset_settings() {
			global $current_section;
			if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
				foreach ( $this->get_settings() as $value ) {
					if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
						delete_option( $value['id'] );
						$autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
						add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
					}
				}
			}
			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_reset_usage_tracking', '' ) ) {
				delete_option( 'pbur_lite_allow_tracking' );
				delete_option( 'alg_wc_price_by_user_role_reset_usage_tracking' );
				Tyche_Plugin_Tracking::reset_tracker_setting( 'pbur_lite' );
			}
		}

		/**
		 * Save settings.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function save() {
			parent::save();
			$this->maybe_reset_settings();
		}

	}

endif;

return new Alg_WC_Settings_Price_By_User_Role();
