<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$order_id = $order->get_id();
$service = $order->get_meta('_deliveryfrom_method', true);
$instance = $order->get_meta('_deliveryfrom_instance', true);
$tracking_link = apply_filters('deliveryfrom_tracking_url_' . $service, false, $order_id, $instance);

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'deliveryfrom' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<?php /* translators: %1$s: Order number, %2$s tracking url */ ?>
<p><?php printf( __( 'Tracking URL for order #%1$s has been generated and is available <a href="%2$s">here</a>', 'deliveryfrom' ), esc_html( $order->get_order_number() ), esc_url($tracking_link) ); ?></p>

<?php
/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
