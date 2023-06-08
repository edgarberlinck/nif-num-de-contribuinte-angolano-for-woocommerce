# NIF (Num. de Contribuinte Angolano) for WooCommerce

Contributors: Edgar Muniz Berlinck
Tags: woocommerce, ecommerce, e-commerce, nif, nipc, vat, tax, portugal, webdados
Author URI: http://edgarberlinck.github.io
Plugin URI: https://github.com/edgarberlinck/nif-num-de-contribuinte-angolano-for-woocommerce
Requires at least: 5.0
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: 1.0.0

This plugin adds the Angola NIF/NIPC as a new field to WooCommerce checkout and order details, if the billing address / customer is from Angola.

## Description

This plugin adds the Angola VAT identification number (NIF/NIPC) as a new field to WooCommerce checkout and order details, if the billing address is from Angola.

### Are you already issuing automatic invoices on your WooCommerce store?

If not, get to know our new plugin: [Invoicing with InvoiceXpress for WooCommerce](https://wordpress.org/plugins/woo-billing-with-invoicexpress/)

### Features:

- Adds the Angola VAT identification number (NIF/NIPC) to the WooCommerce Checkout fields, Order admin fields, Order Emails and "Thank You" page;
- It's possible to edit the customer's NIF/NIPC field on "My Account - Billing Address" and on the User edit screen on wp-admin.
- NIF/NIPC check digit validation (if activated via filter)

### Installation

- Use the included automatic install feature on your WordPress admin panel and search for "NIF WooCommerce".

## Frequently Asked Questions

### How to make the NIF field required?

Just add this to your theme's functions.php file (v3.0 and up):

`add_filter( 'woocommerce_nif_field_required', '__return_true' );`

### Is it possible to validate the check digit in order to ensure a valid Portuguese NIF/NIPC is entered by the customer?

Yes, it is! Just add this to your theme's functions.php file (v3.0 and up):

`add_filter( 'woocommerce_nif_field_validate', '__return_true' );`

We only recommend validating the NIF if your shop only sells to Portugal.

### Is this plugin compliant with the new EU General Data Protection Regulation (GDPR)?

First of all, it's the website owner responsibility to make your whole website compliant because no personal details whatsoever are transmitted to us, the plugin developers.
Anyway, we can help you by documenting how this plugin handles the collected data:

- The NIF/NIPC field is collected via the checkout process and stored as an order meta value, alongside with all the other WooCommerce order fields;
- The NIF/NIPC field can also be set on the "My Account - Billing Address" form and it's then stored as a user meta value, alongside with all the other WordPress and WooCommerce user fields;
- The NIF/NIPC field is shown and editable on the order edit and user edit screens on the backend, by the store owner;
- The NIF/NIPC field is shown on the order transactional emails;

### Is this plugin compatible with the new WooCommerce High-Performance order storage (COT)?

Yes.

### I need help, can I get technical support?

This is a free plugin. It’s our way of giving back to the wonderful WordPress community.

## Changelog

= 1.0 =

- Initial release.
