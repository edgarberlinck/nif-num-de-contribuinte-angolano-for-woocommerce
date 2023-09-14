<?php
/**
 * Plugin Name: NIF (Num. de Contribuinte Angolano) for WooCommerce
 * Plugin URI: https://github.com/edgarberlinck/
 * Description: This plugin adds the Angola VAT identification number (NIF/NIPC) as a new field to WooCommerce checkout and order details, if the billing address is from Angola.
 * Version: 1.0.0
 * Author: Edgar Muniz Berlinck
 * Author URI: http://edgarberlinck.github.io/
 * Text Domain: nif-num-de-contribuinte-angola-for-woocommerce
 * Domain Path: /lang
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WC requires at least: 5.0
 * WC tested up to: 7.6
 **/

/* WooCommerce CRUD ready */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action(
	'plugins_loaded',
	function() {
		if ( class_exists( 'WooCommerce' ) && defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '5.0', '>=' ) ) {

			/**
			 * Init, load textdomain and enqueue scripts
			 */
			function woocommerce_nif_init() {
				load_plugin_textdomain( 'nif-num-de-contribuinte-angolano-for-woocommerce' );
				add_action( 'wp_enqueue_scripts', 'woocommerce_nif_billing_fields_enqueue_scripts' );
			}
			add_action( 'plugins_loaded', 'woocommerce_nif_init' );

			/**
			 * Enqueue Javascript
			 */
			function woocommerce_nif_billing_fields_enqueue_scripts() {
				if ( function_exists( 'is_checkout' ) && is_checkout() && apply_filters( 'woocommerce_nif_use_javascript', true ) ) { // Default - USE Javascript (4.0)
					if ( ! function_exists( 'get_plugin_data' ) ) {
						require_once ABSPATH . 'wp-admin/includes/plugin.php';
					}
					$plugin = get_plugin_data( __FILE__ );
					wp_enqueue_script( 'woocommerce-nif', plugins_url( 'js/functions.js', __FILE__ ), array( 'jquery' ), $plugin['Version'], true );
					wp_localize_script(
						'woocommerce-nif',
						'woocommerce_nif',
						array(
							'show_all_countries' => apply_filters( 'woocommerce_nif_show_all_countries', false ) ? 1 : 0,
							'validate'           => apply_filters( 'woocommerce_nif_field_validate', false ) ? 1 : 0,
						)
					);
				}
			}

			/**
			 * Add field to billing address fields - Globally
			 *
			 * @param array  $fields The billing fields.
			 * @param string $country The billing country.
			 */
			function woocommerce_nif_billing_fields( $fields, $country ) {
				$fields['billing_nif'] = array(
					'type'         => 'text',
					'label'        => apply_filters( 'woocommerce_nif_field_label', __( 'NIF', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
					'placeholder'  => apply_filters( 'woocommerce_nif_field_placeholder', __( 'Aponte o NIF da sua Empresa', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
					'class'        => apply_filters( 'woocommerce_nif_field_class', array( 'form-row-first' ) ), // Should be an option (?)
					'required'     => (
											( $country === 'AO' ) || ( apply_filters( 'woocommerce_nif_show_all_countries', false ) )
											?
											apply_filters( 'woocommerce_nif_field_required', false ) // Should be an option (?)
											:
											false
										),
					'clear'        => apply_filters( 'woocommerce_nif_field_clear', true ), // Should be an option (?)
					'autocomplete' => apply_filters( 'woocommerce_nif_field_autocomplete', 'on' ),
					'priority'     => apply_filters( 'woocommerce_nif_field_priority', 120 ), // WooCommerce should order by this parameter but it doesn't seem to be doing so
					'maxlength'    => apply_filters( 'woocommerce_nif_field_maxlength', 14 ),
					'validate'     => apply_filters( 'woocommerce_nif_field_validate', true ),
				);
				return $fields;
			}
			add_filter( 'woocommerce_billing_fields', 'woocommerce_nif_billing_fields', 10, 2 );

			/**
			 * Add field to order admin panel
			 *
			 * @param array $billing_fields The billing fields.
			 */
			function woocommerce_nif_admin_billing_fields( $billing_fields ) {
				// HPOS - Start
				global $post, $theorder;
				if ( ! empty( $theorder ) ) {
					$order = $theorder;
				} elseif ( isset( $post ) ) {
					$order = wc_get_order( $post->ID );
				}
				// HPOS - End
				if ( ! empty( $order ) ) {
					$countries       = new WC_Countries();
					$billing_country = $order->get_billing_country();
					// Customer is from Angola or it's a new order ?
					if ( $billing_country === 'AO' || ( $billing_country === '' && $countries->get_base_country() === 'AO' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
						$billing_fields['nif'] = array(
							'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
						);
					}
				}
				return $billing_fields;
			}
			add_filter( 'woocommerce_admin_billing_fields', 'woocommerce_nif_admin_billing_fields' );

			/**
			 * Add field to ajax billing get_customer_details - See https://github.com/woothemes/woocommerce/commit/5c43b340027fc9dea78e15825f12191768f7d2ed
			 */
			function woocommerce_nif_admin_init_found_customer_details() {
				add_filter( 'woocommerce_ajax_get_customer_details', 'woocommerce_nif_ajax_get_customer_details', 10, 3 );
			}
			add_action( 'admin_init', 'woocommerce_nif_admin_init_found_customer_details' );

			/**
			 * See https://github.com/woocommerce/woocommerce/issues/12654
			 *
			 * @param array   $customer_data The costumer data.
			 * @param object  $customer The customer.
			 * @param integer $user_id The user ID.
			 */
			function woocommerce_nif_ajax_get_customer_details( $customer_data, $customer, $user_id ) {
				if ( ( isset( $customer_data['billing']['country'] ) && $customer_data['billing']['country'] === 'AO' ) || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) {
					$customer_data['billing']['nif'] = $customer->get_meta( 'billing_nif' );
				}
				return $customer_data;
			}

			/**
			 * Add field to the admin user edit screen
			 *
			 * @param array $show_fields The fields.
			 */
			function woocommerce_nif_customer_meta_fields( $show_fields ) {
				if ( isset( $show_fields['billing'] ) && is_array( $show_fields['billing']['fields'] ) ) {
					$show_fields['billing']['fields']['billing_nif'] = array(
						'label'       => apply_filters( 'woocommerce_nif_field_label', __( 'NIF', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
						'description' => apply_filters( 'woocommerce_nif_field_placeholder', __( 'Num do Contribuinte', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
					);
				}
				return $show_fields;
			}
			add_action( 'woocommerce_customer_meta_fields', 'woocommerce_nif_customer_meta_fields' );

			/**
			 * Add field to customer details on the Thank You page
			 *
			 * @param object $order The WooCommerce order.
			 */
			function woocommerce_nif_order_details_after_customer_details( $order ) {
				$billing_country = $order->get_billing_country();
				$billing_nif     = $order->get_meta( '_billing_nif' );
				if ( ( $billing_country === 'AO' || apply_filters( 'woocommerce_nif_show_all_countries', false ) ) && $billing_nif ) {
					?>
				<tr>
					<th><?php echo esc_html( apply_filters( 'woocommerce_nif_field_label', __( 'NIF', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ) ); ?>:</th>
					<td><?php echo esc_html( $billing_nif ); ?></td>
				</tr>
					<?php
				}
			}
			add_action( 'woocommerce_order_details_after_customer_details', 'woocommerce_nif_order_details_after_customer_details' );

			/**
			 * Add field to customer details on Emails
			 *
			 * @param array  $fields        The fields shown on email.
			 * @param bool   $sent_to_admin If this email is sent to admin.
			 * @param object $order         The WooCommerce order.
			 */
			function woocommerce_nif_email_customer_details_fields( $fields, $sent_to_admin, $order ) {
				$billing_nif = $order->get_meta( '_billing_nif' );
				if ( $billing_nif ) {
					$fields['billing_nif'] = array(
						'label' => apply_filters( 'woocommerce_nif_field_label', __( 'NIF', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ),
						'value' => wptexturize( $billing_nif ),
					);
				}
				return $fields;
			}
			add_filter( 'woocommerce_email_customer_details_fields', 'woocommerce_nif_email_customer_details_fields', 10, 3 );

			/**
			 * Add field to the REST API - Order
			 *
			 * @param array  $order_data The data sent on the REST API request.
			 * @param object $order      The WooCommerce order.
			 */
			function woocommerce_nif_woocommerce_api_order_response( $order_data, $order ) {
				// Order
				if ( isset( $order_data['billing_address'] ) ) {
					$billing_nif                          = $order->get_meta( '_billing_nif' );
					$order_data['billing_address']['nif'] = $billing_nif;
				}
				return $order_data;
			}
			add_filter( 'woocommerce_api_order_response', 'woocommerce_nif_woocommerce_api_order_response', 11, 2 ); // After WooCommerce own add_customer_data

			/**
			 * Add field to the REST API - Customer
			 *
			 * @param array  $customer_data The data sent on the REST API request.
			 * @param object $customer      The WooCommerce customer.
			 */
			function woocommerce_nif_woocommerce_api_customer_response( $customer_data, $customer ) {
				// Customer
				if ( isset( $customer_data['billing_address'] ) ) {
					$billing_nif                             = $customer->get_meta( 'billing_nif' );
					$customer_data['billing_address']['nif'] = $billing_nif;
				}
				return $customer_data;
			}
			add_filter( 'woocommerce_api_customer_response', 'woocommerce_nif_woocommerce_api_customer_response', 10, 2 );

			/**
			 * Validation - Checkout
			 */
			function woocommerce_nif_checkout_process() {
				if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
					$customer_country = WC()->customer->get_billing_country();
					$countries        = new WC_Countries();
					// If the field is NOT required and it's empty, we shouldn't validate it
					if ( $customer_country === 'AO' || ( $customer_country === '' && $countries->get_base_country() === 'AO' ) ) {
						$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' ); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, (because wc_clean takes care of it)
						if (
							!
							(
								woocommerce_valida_nif( $billing_nif )
								||
								( trim( $billing_nif ) === '' && ! apply_filters( 'woocommerce_nif_field_required', false ) )
							)
						) {
							wc_add_notice(
								/* translators: %s NIF field name */
								sprintf( __( 'Você informou um %s Angolano inválido.', 'nif-num-de-contribuinte-angolano-for-woocommerce' ), '<strong>' . apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ) . '</strong>' ),
								'error',
								array(
									'id' => 'billing_nif',
								)
							);
						}
					} //else {
						// Not Portugal
					// }
				} //else {
					// All good - No validation required
				// }
			}
			add_action( 'woocommerce_checkout_process', 'woocommerce_nif_checkout_process' );

			/**
			 * Validation - Save address
			 *
			 * @param int    $user_id      User ID being saved.
			 * @param string $load_address Type of address e.g. billing or shipping.
			 * @param array  $address      The address fields.
			 */
			function woocommerce_nif_after_save_address_validation( $user_id, $load_address, $address ) {
				if ( $load_address === 'billing' ) {
					if ( apply_filters( 'woocommerce_nif_field_validate', false ) ) {
						$country = wc_clean( isset( $_POST['billing_country'] ) ? $_POST['billing_country'] : '' ); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, (because wc_clean takes care of it)
						if ( $country === 'AO' ) {
							$billing_nif = wc_clean( isset( $_POST['billing_nif'] ) ? $_POST['billing_nif'] : '' ); //phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, (because wc_clean takes care of it)
							// If the field is NOT required and it's empty, we shouldn't validate it
							if (
								!
								(
									woocommerce_valida_nif( $billing_nif )
									||
									( trim( $billing_nif ) === '' && ! apply_filters( 'woocommerce_nif_field_required', false ) )
								)
							) {
								wc_add_notice(
									/* translators: %s NIF field name */
									sprintf( __( 'Você informaou um %s Angolano inválido.', 'nif-num-de-contribuinte-angolano-for-woocommerce' ), '<strong>' . apply_filters( 'woocommerce_nif_field_label', __( 'NIF / NIPC', 'nif-num-de-contribuinte-angolano-for-woocommerce' ) ) . '</strong>' ),
									'error'
								);
							}
						}
					}
				}
			}
			add_action( 'woocommerce_after_save_address_validation', 'woocommerce_nif_after_save_address_validation', 10, 3 );

			/**
			 * NIF Validation
			 *
			 * @param string $nif         The NIF number.
			 * @param bool   $ignore_first Ignore first digit validation or not.
			 */
			function woocommerce_valida_nif( $nif ) {
				$len = strlen(trim( $nif ));
				$valid_len = array(10, 14);

				return in_array($len, $valid_len);
				
			}
		}
	}
);

/* HPOS Compatible */
add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);