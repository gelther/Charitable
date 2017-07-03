<?php
/**
 * email_preview endpoint.
 *
 * @version     1.5.0
 * @package     Charitable/Classes/Charitable_Email_Preview_Endpoint
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Email_Preview_Endpoint' ) ) :

	/**
	 * Charitable_Email_Preview_Endpoint
	 *
	 * @abstract
	 * @since       1.5.0
	 */
	class Charitable_Email_Preview_Endpoint extends Charitable_Endpoint {

		/**
		 * @var     string
		 */
		const ID = 'email_preview';

		/**
		 * Return the endpoint ID.
		 *
		 * @return  string
		 * @access 	public
		 * @static
		 * @since 	1.5.0
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Return the endpoint URL.
		 *
		 * @param   array   $args
		 * @return  string
		 * @access  public
		 * @since   1.5.0
		 */
		public function get_page_url( $args = array() ) {
			if ( ! array_key_exists( 'email_id', $args ) ) {

				charitable_get_deprecated()->doing_it_wrong(
					__METHOD__,
					__( 'Missing email_id in args for email preview endpoint URL.', 'charitable' ),
					'1.5.0'
				);

				return '';

			}

			return esc_url_raw( add_query_arg( array(
				'charitable_action' => 'preview_email',
				'email_id'          => $args['email_id'],
			), home_url() ) );
		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @param   array    $args
		 * @return  boolean
		 * @access  public
		 * @since   1.5.0
		 */
		public function is_page( $args = array() ) {
			return array_key_exists( 'charitable_action', $_GET ) && 'preview_email' == $_GET['charitable_action'];
		}

		/**
		 * Return the template to display for this endpoint.
		 *
		 * @param   string  $template  The default template.
		 * @return  string
		 * @access  public
		 * @since   1.5.0
		 */
		public function get_template( $template ) {
			do_action( 'charitable_email_preview' );

			return 'emails/preview.php';
		}

	}

endif;
