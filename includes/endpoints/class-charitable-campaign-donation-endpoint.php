<?php
/**
 * donate endpoint.
 *
 * @version     1.5.0
 * @package     Charitable/Classes/Charitable_Campaign_Donation_Endpoint
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Campaign_Donation_Endpoint' ) ) :

	/**
	 * Charitable_Campaign_Donation_Endpoint
	 *
	 * @abstract
	 * @since       1.5.0
	 */
	class Charitable_Campaign_Donation_Endpoint extends Charitable_Endpoint {

		/**
		 * @var     string
		 */
		const ID = 'campaign_donation';

		/**
		 * Return the endpoint ID.
		 *
		 * @return string
		 * @access 	public
		 * @static
		 * @since 	1.5.0
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Add rewrite rules for the endpoint.
		 *
		 * @access 	public
		 * @since 	1.5.0
		 */
		public function setup_rewrite_rules() {
			add_rewrite_endpoint( 'donate', EP_PERMALINK );
		}

		/**
		 * Return the endpoint URL.
		 *
		 * @global 	WP_Rewrite $wp_rewrite
		 * @param  array  $args
		 * @return string
		 * @access  public
		 * @since   1.5.0
		 */
		public function get_page_url( $args = array() ) {
			global $wp_rewrite;

			$campaign_id  = array_key_exists( 'campaign_id', $args ) ? $args['campaign_id'] : get_the_ID();
			$campaign_url = get_permalink( $campaign_id );

			if ( 'same_page' == charitable_get_option( 'donation_form_display', 'separate_page' ) ) {
				return $campaign_url;
			}

			if ( $wp_rewrite->using_permalinks()
				&& ! in_array( get_post_status( $campaign_id ), array( 'pending', 'draft' ) )
				&& ! isset( $_GET['preview'] ) ) {
				return trailingslashit( $campaign_url ) . 'donate/';
			}

			return esc_url_raw( add_query_arg( array( 'donate' => 1 ), $campaign_url ) );
		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @global  WP_Query $wp_query
		 * @param  array   $args
		 * @return boolean
		 * @access  public
		 * @since   1.5.0
		 */
		public function is_page( $args = array() ) {
			global $wp_query;

			if ( ! $wp_query->is_singular( Charitable::CAMPAIGN_POST_TYPE ) ) {
				return false;
			}

			if ( array_key_exists( 'donate', $wp_query->query_vars ) ) {
				return true;
			}

			/* If 'strict' is set to `true`, this will only return true if this has the /donate/ endpoint. */
			if ( array_key_exists( 'strict', $args ) && $args['strict'] ) {
				return false;
			}

			return 'separate_page' != charitable_get_option( 'donation_form_display', 'separate_page' );
		}

		/**
		 * Return the template to display for this endpoint.
		 *
		 * @param  string $template The default template.
		 * @return string
		 * @access  public
		 * @since   1.5.0
		 */
		public function get_template( $template ) {
			/* If a donation ID is included, make sure it belongs to the current user. */
			$donation_id = get_query_var( 'donation_id', false );

			if ( $donation_id ) {

				$donation = charitable_get_donation( $donation_id );

				if ( ! $donation || ! $donation->is_from_current_user() ) {

					wp_safe_redirect( charitable_get_permalink( 'campaign_donation' ) );
					exit();

				}
			}

			/* If the campaign has exired, redirect the user to the campaign page. */
			$campaign = charitable_get_current_campaign();

			if ( ! $campaign || $campaign->has_ended() ) {

				wp_safe_redirect( get_permalink( $campaign->ID ) );
				exit();

			}

			do_action( 'charitable_is_donate_page' );

			return array( 'campaign-donation-page.php', 'page.php', 'index.php' );
		}

		/**
		 * Get the content to display for the endpoint.
		 *
		 * @param  string $content
		 * @return string
		 * @access  public
		 * @since   1.5.0
		 */
		public function get_content( $content ) {
			if ( ! charitable_is_main_loop() ) {
				return $content;
			}

			if ( 'separate_page' != charitable_get_option( 'donation_form_display', 'separate_page' )
				&& false === get_query_var( 'donate', false ) ) {
				return $content;
			}

			ob_start();

			charitable_template( 'content-donation-form.php' );

			return ob_get_clean();
		}

	}

endif;
