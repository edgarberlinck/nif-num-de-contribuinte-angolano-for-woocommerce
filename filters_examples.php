<?php
/**
 * Hooks examples
 **/

/**
 * Change NIF field label
 *
 * @param string $label The label.
 */
function woocommerce_nif_field_label( $label ) {
	// Default is 'NIF / NIPC'
	return 'NIF';
}
add_filter( 'woocommerce_nif_field_label', 'woocommerce_nif_field_label' );

/**
 * Change NIF field placeholder
 *
 * @param string $placeholder The placeholder.
 */
function woocommerce_nif_field_placeholder( $placeholder ) {
	// Default is 'Portuguese VAT identification number'
	return 'VAT number';
}
add_filter( 'woocommerce_nif_field_placeholder', 'woocommerce_nif_field_placeholder' );

// Make NIF field required.
add_filter( 'woocommerce_nif_field_required', '__return_true' );

/**
 * Make NIF field wide
 *
 * @param string $class The CSS class.
 */
function woocommerce_nif_field_class( $class ) {
	// Default is form-row-first
	$class = array(
		'form-row-wide',
	);
	return $class;
}
add_filter( 'woocommerce_nif_field_class', 'woocommerce_nif_field_class' );

// Make NIF field not clear.
add_filter( 'woocommerce_nif_field_clear', '__return_false' );

/**
 * Disable autocomplete for NIF field
 *
 * @param string $autocomplete Autocomplete on or off.
 */
function woocommerce_nif_field_autocomplete( $autocomplete ) {
	// Default is 'on'.
	return 'off';
}
add_filter( 'woocommerce_nif_field_autocomplete', 'woocommerce_nif_field_autocomplete' );

/**
 * Change NIF field priority
 *
 * @param integer $priority NIF field priority.
 */
function woocommerce_nif_field_priority( $priority ) {
	// Default is 120.
	return 1;
}
add_filter( 'woocommerce_nif_field_priority', 'woocommerce_nif_field_priority' );

/**
 * Change NIF field maxlength
 *
 * @param integer $maxlength NIF field maxium length.
 */
function woocommerce_nif_field_maxlength( $maxlength ) {
	// Default is 9.
	return 10;
}
add_filter( 'woocommerce_nif_field_maxlength', 'woocommerce_nif_field_maxlength' );

// Validate the NIF check digit?
add_filter( 'woocommerce_nif_field_validate', '__return_true' );

// De-activate the NIF field javascript toggle on the checkout page, and use the old mechanism.
add_filter( 'woocommerce_nif_use_javascript', '__return_false' );
