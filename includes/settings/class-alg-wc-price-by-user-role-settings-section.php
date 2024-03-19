<?php
/**
 * Price by User Role for WooCommerce - Section Settings
 *
 * @package PriceByUserRole
 * @version 1.2.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Price_By_User_Role_Settings_Section' ) ) :

	/**
	 * Alg_WC_Price_By_User_Role_Settings_Section Class
	 *
	 * @class   Alg_WC_Price_By_User_Role_Settings_Section
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	class Alg_WC_Price_By_User_Role_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			add_filter( 'woocommerce_get_sections_alg_wc_price_by_user_role', array( $this, 'settings_section' ) );
			add_filter( 'woocommerce_get_settings_alg_wc_price_by_user_role_' . $this->id, array( $this, 'get_settings' ), PHP_INT_MAX );
		}

		/**
		 * Settings_section.
		 *
		 * @param array $sections Section for Settings.
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function settings_section( $sections ) {
			$sections[ $this->id ] = $this->desc;
			return $sections;
		}

		/**
		 * Get_settings.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function get_settings() {
			return array_merge(
				$this->get_section_settings(),
				array(
					array(
						'title' => __( 'Reset Settings', 'price-by-user-role-for-woocommerce' ),
						'type'  => 'title',
						'id'    => 'alg_wc_price_by_user_role_' . $this->id . '_reset_options',
					),
					array(
						'title'   => __( 'Reset section settings', 'price-by-user-role-for-woocommerce' ),
						'desc'    => '<strong>' . __( 'Reset', 'price-by-user-role-for-woocommerce' ) . '</strong>',
						'id'      => 'alg_wc_price_by_user_role_' . $this->id . '_reset',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'title'   => __( 'Reset Usage Tracking', 'price-by-user-role-for-woocommerce' ),
						'desc'    => __( 'This will reset your usage tracking settings, causing it to show the opt-in banner again and not sending any data.', 'price-by-user-role-for-woocommerce' ),
						'id'      => 'alg_wc_price_by_user_role_reset_usage_tracking',
						'default' => 'no',
						'type'    => 'checkbox',
					),
					array(
						'type' => 'sectionend',
						'id'   => 'alg_wc_price_by_user_role_' . $this->id . '_reset_options',
					),
				)
			);
		}

	}

endif;
