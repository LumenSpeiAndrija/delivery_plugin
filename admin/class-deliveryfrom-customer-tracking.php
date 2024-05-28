<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

/**
 * Class DeliveryFrom_Customer_Tracking_Email
 */
class DeliveryFrom_Customer_Tracking_Email extends WC_Email {

	/**
	 * Create an instance of the class.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {
        // Email slug we can use to filter other data.
		$this->id          = 'deliveryfrom_customer_tracking';
        $this->enabled          = 'no';
		$this->title       = __( 'Tracking order for customer', 'deliveryfrom' );
		$this->description = __( 'Email sent to customer once tracking number is generated.', 'deliveryfrom' );
        // For admin area to let the user know we are sending this email to customers.
		$this->customer_email = true;
		$this->heading     = __( 'Order tracking', 'deliveryfrom' );
		// translators: placeholder is {blogname}, a variable that will be substituted when email is sent out
		$this->subject     = sprintf( _x( '[%s] Order tracking', 'default email subject for order tracking emails sent to the customer', 'deliveryfrom' ), '{blogname}' );
    
        // Template paths.
		$this->template_html  = 'emails/wc-deliveryfrom-tracking.php';
		$this->template_plain = 'emails/plain/wc-deliveryfrom-tracking.php';
		$this->template_base  = DELIVERYFROM_DIR . 'templates/';

		parent::__construct();
	}

    function trigger( $order_id ) {
		$this->object = wc_get_order( $order_id );

		if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
			$order_email = $this->object->billing_email;
		} else {
			$order_email = $this->object->get_billing_email();
		}

		$this->recipient = $order_email;


		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

      /**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
            'additional_content' => $this->get_additional_content(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this
		), '', $this->template_base );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
            'additional_content' => $this->get_additional_content(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this
		), '', $this->template_base );
	}
}