<?php
/**
 * Price by User Role for WooCommerce - Per Product Section Settings
 *
 * @package PriceByUserRole
 * @version 1.2.0
 * @since   1.0.0
 * @author  Tyche Softwares
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Alg_WC_Price_By_User_Role_Settings_Per_Product' ) ) :

	/**
	 * Alg_WC_Price_By_User_Role_Settings_Per_Product Class
	 *
	 * @class   Alg_WC_Price_By_User_Role_Settings_Per_Product
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	class Alg_WC_Price_By_User_Role_Settings_Per_Product extends Alg_WC_Price_By_User_Role_Settings_Section {

		/**
		 * Constructor.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function __construct() {
			$this->id   = 'per_product';
			$this->desc = __( 'Per Product', 'price-by-user-role-for-woocommerce' );
			parent::__construct();

			if ( 'yes' === get_option( 'alg_wc_price_by_user_role_enabled', 'yes' ) ) {
				if ( 'yes' === get_option( 'alg_wc_price_by_user_role_per_product_enabled', 'yes' ) ) {
					add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
					add_action( 'save_post_product', array( $this, 'save_meta_box' ), PHP_INT_MAX, 2 );
					add_filter( 'alg_wc_price_by_user_role_save_meta_box_value', array( $this, 'save_meta_box_value' ), PHP_INT_MAX, 3 );
					add_action( 'admin_notices', array( $this, 'admin_notices' ) );
					add_action( 'admin_init', array( $this, 'price_enqueue' ) );
				}
			}
		}

		/**
		 * Save_meta_box_value.
		 *
		 * @param string $option_value Value of the option.
		 * @param string $option_name Name of the option.
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function save_meta_box_value( $option_value, $option_name ) {
			if ( true === apply_filters( 'alg_wc_price_by_user_role', false, 'per_product_settings' ) ) {
				return $option_value;
			}
			if ( 'no' === $option_value ) {
				return $option_value;
			}
			if ( 'alg_wc_price_by_user_role_per_product_settings_enabled' === $option_name ) {
				$args = array(
					'post_type'      => 'product',
					'post_status'    => 'any',
					'posts_per_page' => 1,
					'meta_key'       => '_alg_wc_price_by_user_role_per_product_settings_enabled', // phpcs:ignore
					'meta_value'     => 'yes', // phpcs:ignore
					'post__not_in'   => array( get_the_ID() ),
					'fields'         => 'ids',
				);
				$loop = new WP_Query( $args );
				$c    = $loop->found_posts + 1;
				/*if ( $c >= 2 ) {
					add_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
					return 'no';
				}*/
			}
			return $option_value;
		}

		/**
		 * Add_notice_query_var.
		 *
		 * @param string $location Location.
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_notice_query_var( $location ) {
			remove_filter( 'redirect_post_location', array( $this, 'add_notice_query_var' ), 99 );
			return add_query_arg( array( 'alg_product_price_by_user_role_admin_notice' => true ), $location );
		}

		/**
		 * Admin_notices.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function admin_notices() {
			if ( ! isset( $_GET['alg_product_price_by_user_role_admin_notice'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
				return;
			}
			?>
			<div class="error"><p>
			<?php echo '<div class="message">'; ?>
			<?php /* translators: %s: Link to pro version */ ?>
			<?php echo sprintf( wp_kses_post( __( 'Free plugins version is limited to only one "price by user role per products settings" product enabled at a time. You will need to get <strong><a href="%s" target="_blank">Product Prices by User Roles for WooCommerce Pro</a></strong> to add unlimited number of "price by user role per product settings" products.', 'price-by-user-role-for-woocommerce' ) ), esc_url( 'https://woocommerce.com/products/product-prices-by-user-roles-for-woocommerce/' ) ); ?>
			<?php echo '</div>'; ?>
			</p></div>
			<?php
		}

		/**
		 * Save_meta_box.
		 *
		 * @param int $post_id Post ID.
		 * @param obj $post Post Object.
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function save_meta_box( $post_id, $post ) {
			// Check that we are saving with current metabox displayed.
			if ( ! isset( $_POST[ 'alg_wc_price_by_user_role_' . $this->id . '_save_post' ] ) ) {
				return;
			}
			// Save options.
			foreach ( $this->get_meta_box_options() as $option ) {
				if ( 'title' === $option['type'] ) {
					continue;
				}
				$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
				if ( $is_enabled ) {
					$option_value  = ( isset( $_POST[ $option['name'] ] ) ) ? sanitize_text_field( wp_unslash( $_POST[ $option['name'] ] ) ) : $option['default'];
					$the_post_id   = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $post_id;
					$the_meta_name = ( isset( $option['meta_name'] ) ) ? $option['meta_name'] : '_' . $option['name'];
					update_post_meta( $the_post_id, $the_meta_name, apply_filters( 'alg_wc_price_by_user_role_save_meta_box_value', $option_value, $option['name'] ) );
				}
			}
		}

		/**
		 * Add_meta_box.
		 *
		 * @version 1.0.0
		 * @since   1.0.0
		 */
		public function add_meta_box() {
			add_meta_box(
				'alg_wc_price_by_user_role_' . $this->id,
				__( 'Product Prices by User Roles: Per Product Settings', 'price-by-user-role-for-woocommerce' ),
				array( $this, 'create_meta_box' ),
				'product',
				'normal',
				'high'
			);
		}

		/**
		 * Create_meta_box.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function create_meta_box() {
			$current_post_id = get_the_ID();
			$html            = '';

			$options_data    = $this->get_meta_box_options();
			$feature_enabled = array_shift( $options_data );

			// add the enable/disable field.
			$html .= '<div id="feature_enabled"><p><strong>';
			$html .= __( $feature_enabled['title'], 'price-by-user-role-for-woocommerce' ); //phpcs:ignore
			$html .= ':</strong>&nbsp;&nbsp;';

			$prices_enabled = get_post_meta( $current_post_id, '_' . $feature_enabled['name'], true ) ? get_post_meta( $current_post_id, '_' . $feature_enabled['name'], true ) : $feature_enabled['default'];
			$options        = '';
			foreach ( $feature_enabled['options'] as $data_option_key => $data_option_value ) {
				$selected = '';
				$selected = selected( $prices_enabled, $data_option_key, false );

				$options .= '<option value="' . $data_option_key . '" ' . $selected . '>' . $data_option_value . '</option>';
			}
			$html .= '<select id="' . $feature_enabled['name'] . '" name="' . $feature_enabled['name'] . '" class="price_enabled">' . $options . '</select></p></div>';

			$display = 'yes' === $prices_enabled ? 'block' : 'none';

			// the other fields.
			$html .= '<div class="price_by_roles_display" style="display:' . $display . ';"><table class="widefat striped">';
			foreach ( $options_data as $option ) {
				$is_enabled = ( isset( $option['enabled'] ) && 'no' === $option['enabled'] ) ? false : true;
				if ( $is_enabled ) {
					if ( 'title' === $option['type'] ) {
						$html .= '<tr>';
						$html .= '<th colspan="3" style="text-align: left; background-color: #e0e0e0;">' . $option['title'] . '</th>';
						$html .= '</tr>';
					} else {
						$custom_attributes = '';
						$the_post_id       = ( isset( $option['product_id'] ) ) ? $option['product_id'] : $current_post_id;
						$the_meta_name     = ( isset( $option['meta_name'] ) ) ? $option['meta_name'] : '_' . $option['name'];
						if ( get_post_meta( $the_post_id, $the_meta_name ) ) {
							$option_value = get_post_meta( $the_post_id, $the_meta_name, true );
						} else {
							$option_value = ( isset( $option['default'] ) ) ? $option['default'] : '';
						}
						$input_ending = '';
						if ( 'select' === $option['type'] ) {
							if ( isset( $option['multiple'] ) ) {
								$custom_attributes = ' multiple';
								$option_name       = $option['name'] . '[]';
							} else {
								$option_name = $option['name'];
							}
							$options = '';
							foreach ( $option['options'] as $select_option_key => $select_option_value ) {
								$selected = '';
								if ( is_array( $option_value ) ) {
									foreach ( $option_value as $single_option_value ) {
										$selected .= selected( $single_option_value, $select_option_key, false );
									}
								} else {
									$selected = selected( $option_value, $select_option_key, false );
								}
								$options .= '<option value="' . $select_option_key . '" ' . $selected . '>' . $select_option_value . '</option>';
							}
						} else {
							$input_ending = ' id="' . $option['name'] . '" name="' . $option['name'] . '" value="' . $option_value . '">';
							if ( isset( $option['custom_attributes'] ) ) {
								$input_ending = ' ' . $option['custom_attributes'] . $input_ending;
							}
						}
						switch ( $option['type'] ) {
							case 'price':
								$field_html = '<input class="short wc_input_price" type="number" step="0.0001"' . $input_ending;
								break;
							case 'date':
								$field_html = '<input class="input-text" display="date" type="text"' . $input_ending;
								break;
							case 'textarea':
								$field_html = '<textarea style="min-width:300px;" id="' . $option['name'] . '" name="' . $option['name'] . '">' . $option_value . '</textarea>';
								break;
							case 'select':
								$field_html = '<select' . $custom_attributes . ' id="' . $option['name'] . '" name="' . $option_name . '">' . $options . '</select>';
								break;
							default:
								$field_html = '<input class="short" type="' . $option['type'] . '"' . $input_ending;
								break;
						}
						$html         .= '<tr>';
						$maybe_tooltip = ( ! empty( $option['tooltip'] ) ? wc_help_tip( $option['tooltip'], true ) : '' );
						$html         .= '<th style="text-align:left;width:150px;">' . $option['title'] . $maybe_tooltip . '</th>';
						if ( isset( $option['desc'] ) && '' !== $option['desc'] ) {
							$html .= '<td style="font-style:italic;">' . $option['desc'] . '</td>';
						}
						$html .= ( 'alg_wc_price_by_user_role_per_product_settings_enabled' === $option['name'] ) ? '<td colspan="2">' . $field_html . '</td>' : '<td>' . $field_html . '</td>';
						$html .= '</tr>';
					}
				}
			}
			$html .= '</table></div>';
			$html .= '<input type="hidden" name="alg_wc_price_by_user_role_' . $this->id . '_save_post" value="alg_wc_price_by_user_role_' . $this->id . '_save_post">';
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Get_meta_box_options.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 * @todo    fix "Make Empty Price" option for variable products
		 */
		public function get_meta_box_options() {
			$main_product_id = get_the_ID();
			$_product        = wc_get_product( $main_product_id );
			$products        = array();
			if ( $_product->is_type( 'variable' ) ) {
				foreach ( $_product->get_children() as $variation_id ) {
					$products[ $variation_id ] = ' (' . alg_get_product_formatted_variation( wc_get_product( $variation_id ), true ) . ')';
				}
			} else {
				$products[ $main_product_id ] = '';
			}
			$options = array(
				array(
					'name'    => 'alg_wc_price_by_user_role_per_product_settings_enabled',
					'tooltip' => __( 'Press Update after changing this value.', 'price-by-user-role-for-woocommerce' ),
					'default' => 'no',
					'type'    => 'select',
					'options' => array(
						'yes' => __( 'Yes', 'price-by-user-role-for-woocommerce' ),
						'no'  => __( 'No', 'price-by-user-role-for-woocommerce' ),
					),
					'title'   => '<strong>' . __( 'Enabled', 'price-by-user-role-for-woocommerce' ) . '</strong>',
				),
			);

			$visible_roles = get_option( 'alg_wc_price_by_user_role_per_product_show_roles', '' );
			foreach ( $products as $product_id => $desc ) {
				foreach ( alg_get_user_roles() as $role_key => $role_data ) {
					if ( ! empty( $visible_roles ) ) {
						if ( ! in_array( $role_key, $visible_roles, true ) ) {
							continue;
						}
					}
					$options = array_merge(
						$options,
						array(
							array(
								'type'  => 'title',
								'title' => '<h4><em>' . $role_data['name'] . '</em></h4>',
							),
							array(
								'name'       => 'alg_wc_price_by_user_role_regular_price_' . $role_key . '_' . $product_id,
								'default'    => '',
								'type'       => 'price',
								'title'      => __( 'Regular price', 'price-by-user-role-for-woocommerce' ),
								'desc'       => $desc,
								'product_id' => $product_id,
								'meta_name'  => '_alg_wc_price_by_user_role_regular_price_' . $role_key,
							),
							array(
								'name'       => 'alg_wc_price_by_user_role_sale_price_' . $role_key . '_' . $product_id,
								'default'    => '',
								'type'       => 'price',
								'title'      => __( 'Sale price', 'price-by-user-role-for-woocommerce' ),
								'desc'       => $desc,
								'product_id' => $product_id,
								'meta_name'  => '_alg_wc_price_by_user_role_sale_price_' . $role_key,
							),
							array(
								'name'       => 'alg_wc_price_by_user_role_empty_price_' . $role_key . '_' . $product_id,
								'default'    => 'no',
								'type'       => 'select',
								'options'    => array(
									'yes' => __( 'Yes', 'price-by-user-role-for-woocommerce' ),
									'no'  => __( 'No', 'price-by-user-role-for-woocommerce' ),
								),
								'title'      => __( 'Make "empty price"', 'price-by-user-role-for-woocommerce' ),
								'desc'       => $desc,
								'product_id' => $product_id,
								'meta_name'  => '_alg_wc_price_by_user_role_empty_price_' . $role_key,
							),
						)
					);
				}
			}
			return $options;
		}

		/**
		 * Get_section_settings.
		 *
		 * @version 1.2.0
		 * @since   1.0.0
		 */
		public function get_section_settings() {
			$settings = array(
				array(
					'title' => __( 'Per Product Options', 'price-by-user-role-for-woocommerce' ),
					'type'  => 'title',
					'id'    => 'alg_wc_price_by_user_role_per_product_options',
				),
				array(
					'title'    => __( 'Enable per product settings', 'price-by-user-role-for-woocommerce' ),
					'desc'     => '<strong>' . __( 'Enable section', 'price-by-user-role-for-woocommerce' ) . '</strong>',
					'desc_tip' => __( 'When enabled, this will add new "Product Prices by User Roles: Per Product Settings" meta box to each product\'s edit page.', 'price-by-user-role-for-woocommerce' ),
					'type'     => 'checkbox',
					'id'       => 'alg_wc_price_by_user_role_per_product_enabled',
					'default'  => 'yes',
				),
				array(
					'title'    => __( 'Show roles on per product settings', 'price-by-user-role-for-woocommerce' ),
					'desc_tip' => __( 'If per product settings is enabled, you can choose which roles to show on product\'s edit page. Leave blank to show all roles.', 'price-by-user-role-for-woocommerce' ),
					'type'     => 'multiselect',
					'id'       => 'alg_wc_price_by_user_role_per_product_show_roles',
					'default'  => '',
					'class'    => 'chosen_select',
					'options'  => alg_get_user_roles_options(),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'alg_wc_price_by_user_role_per_product_options',
				),
			);
			return $settings;
		}

		/**
		 * Add JS files on Edit Product Page.
		 *
		 * @since 1.3
		 */
		public function price_enqueue() {
			if ( isset( $_GET['post'] ) && $_GET['post'] > 0 && get_post_type( sanitize_text_field( wp_unslash( $_GET['post'] ) ) ) === 'product' ) {
				wp_enqueue_script( 'price-roles-admin-js', plugins_url() . '/price-by-user-role-for-woocommerce/assets/js/product-settings-admin.js', array( 'jquery' ), alg_wc_price_by_user_role()->version, true );
			}
		}
	}

endif;

return new Alg_WC_Price_By_User_Role_Settings_Per_Product();
