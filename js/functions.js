jQuery(
	function( $ ) {

		if ( $( 'form.checkout' ).length === 0 ) {
			return;
		}

		var checkout_form = $( 'form.checkout' );
		var current_nif   = '';
		var nif_input     = $( '#billing_nif' );
		var nif_container = nif_input.closest( '.form-row' )
		// Only for Angola?
		if ( woocommerce_nif.show_all_countries == 0 ) {
			checkout_form.on(
				'change',
				'#billing_country',
				function() {
					var country = $( '#billing_country' ).val();

					if ( country == 'AO' ) {
						if ( nif_container.is( ':hidden' ) ) {
							nif_container.show();
							if ( current_nif != '' ) {
								nif_input.val( current_nif );
							}
							current_nif = '';
						}
					} else {
						if ( nif_container.is( ':visible' ) ) {
							current_nif = nif_input.val();
							nif_input.val( '' );
							nif_container.hide();
						}
					}
				}
			);
		}

		// Validation?
		if ( woocommerce_nif.validate == 1 ) {
			$( document.body ).on(
				'checkout_error',
				function() {
					if ( $( '.woocommerce-error li' ).length ) {
						$( '.woocommerce-error' ).find( 'li' ).each(
							function() {
								if ( $( this ).data( 'id' ) == 'billing_nif' ) {
									nif_container.removeClass( 'woocommerce-validated' ).addClass( 'woocommerce-invalid' );
								}
							}
						);
					}
				}
			);
		}

	}
);
