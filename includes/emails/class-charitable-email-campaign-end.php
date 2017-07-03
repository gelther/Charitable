<?php
/**
 * Class that models the campaign end email.
 *
 * @version     1.1.0
 * @package     Charitable/Classes/Charitable_Email_Campaign_End
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Email_Campaign_End' ) ) :

	/**
	 * Campaign End Email
	 *
	 * @since       1.1.0
	 */
	class Charitable_Email_Campaign_End extends Charitable_Email {

		/**
		 * @var     string
		 */
		const ID = 'campaign_end';

		/**
		 * @var     boolean Whether the email allows you to define the email recipients.
		 * @access  protected
		 * @since   1.1.0
		 */
		protected $has_recipient_field = true;

		/**
		 * @var     string[] Array of supported object types (campaigns, donations, donors, etc).
		 * @access  protected
		 * @since   1.1.0
		 */
		protected $object_types = array( 'campaign' );

		/**
		 * Instantiate the email class, defining its key values.
		 *
		 * @param mixed[] $objects
		 * @access  public
		 * @since   1.1.0
		 */
		public function __construct( $objects = array() ) {
			parent::__construct( $objects );

			$this->name = apply_filters( 'charitable_email_campaign_end_name', __( 'Admin: Campaign Ended Notification', 'charitable' ) );
		}

		/**
		 * Returns the current email's ID.
		 *
		 * @return string
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function get_email_id() {
			return self::ID;
		}

		/**
		 * Static method that is fired within 24 hours after a campaign is finished.
		 *
		 * @param  int     $campaign_id
		 * @return boolean
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function send_with_campaign_id( $campaign_id ) {
			if ( ! charitable_get_helper( 'emails' )->is_enabled_email( self::get_email_id() ) ) {
				return false;
			}

			$email = new Charitable_Email_Campaign_End( array(
				'campaign' => new Charitable_Campaign( $campaign_id ),
			) );

			/**
			 * Don't resend the email.
			 */
			if ( $email->is_sent_already( $campaign_id ) ) {
				return false;
			}

			/**
			 * Check whether the campaign expired in the last 24 hours.
			 */
			if ( ! $email->is_time_to_send() ) {
				return false;
			}

			$sent = $email->send();

			/**
			 * Log that the email was sent.
			 */
			if ( apply_filters( 'charitable_log_email_send', true, self::get_email_id(), $email ) ) {
				$email->log( $campaign_id, $sent );
			}

			return true;
		}

		/**
		 * Returns whether it is time to send the email.
		 *
		 * This returns true if the campaign has expired in the last 24 hours.
		 *
		 * @return boolean
		 * @access  public
		 * @since   1.3.2
		 */
		public function is_time_to_send() {
			$time_since_ended = $this->get_campaign()->get_time_since_ended();
			return $time_since_ended > 0 && $time_since_ended <= 86400;
		}

		/**
		 * Return the default recipient for the email.
		 *
		 * @return string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_recipient() {
			return get_option( 'admin_email' );
		}

		/**
		 * Return the default subject line for the email.
		 *
		 * @return string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_subject() {
			return __( 'A campaign has finished', 'charitable' );
		}

		/**
		 * Return the default headline for the email.
		 *
		 * @return string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_headline() {
			return apply_filters( 'charitable_email_campaign_end_default_headline', __( 'Campaign has ended', 'charitable' ), $this );
		}

		/**
		 * Return the default body for the email.
		 *
		 * @return string
		 * @access  protected
		 * @since   1.1.0
		 */
		protected function get_default_body() {
			ob_start();
?>
<p><?php _e( '[charitable_email show=campaign_title] by [charitable_email show=campaign_creator] finished on [charitable_email show=campaign_end_date].', 'charitable' ); ?></p>
<p>[charitable_email show=campaign_achieved_goal success="<?php _e( 'The campaign achieved its fundraising goal.', 'charitable' ); ?>" failure="<?php _e( 'The campaign did not reach its fundraising goal.', 'charitable' ); ?>"]</p>
<table>
<tbody>
<tr>
	<th><?php _e( 'Amount raised', 'charitable' ); ?></th>
	<td>[charitable_email show=campaign_donated_amount]</td>
</tr>
<tr>
	<th><?php _e( 'Number of donors', 'charitable' ); ?></th>
	<td>[charitable_email show=campaign_donor_count]</td>
</tr>
<tr>
	<th><?php _e( 'Fundraising goal', 'charitable' ); ?></th>
	<td>[charitable_email show=campaign_goal]</td>
</tr>
</tbody>
</table>
<?php
			$body = ob_get_clean();

			return apply_filters( 'charitable_email_campaign_end_default_body', $body, $this );
		}

	}

endif;
