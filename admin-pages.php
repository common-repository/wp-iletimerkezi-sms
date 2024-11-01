<?php
/**
 * Display the General settings tab
 * @return void
 */
function ilt_display_tab_general( $tab, $page_url ) {
	if( $tab != 'general' ) {
		return;
	}
	$options = get_option( ILT_CORE_OPTION );
	?>
	<form method="post" action="options.php">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Iletimerkezi Public Key', ILT_TD ); ?><br /><span style="font-size: x-small;"><?php _e( 'Available from within your iletimerkezi.com account', ILT_TD ); ?></span></th>
				<td>
					<input size="50" type="text" name="<?php echo ILT_CORE_OPTION; ?>[public_key]" placeholder="<?php _e( 'Enter Public Key', ILT_TD ); ?>" value="<?php echo htmlspecialchars( $options['public_key'] ); ?>" class="regular-text" />
					<br />
					<small><?php _e( 'To view API credentials visit <a href="https://www.iletimerkezi.com" target="_blank">https://www.iletimerkezi.com</a>', ILT_TD ); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Iletimerkezi Private Key', ILT_TD ); ?><br /><span style="font-size: x-small;"><?php _e( 'Available from within your iletimerkezi.com account', ILT_TD ); ?></span></th>
				<td>
					<input size="50" type="text" name="<?php echo ILT_CORE_OPTION; ?>[private_key]" placeholder="<?php _e( 'Enter Private Key', ILT_TD ); ?>" value="<?php echo htmlspecialchars( $options['private_key'] ); ?>" class="regular-text" />
					<br />
					<small><?php _e( 'To view API credentials visit <a href="https://www.iletimerkezi.com" target="_blank">https://www.iletimerkezi.com</a>', ILT_TD ); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Iletimerkezi Sender', ILT_TD ); ?><br /><span style="font-size: x-small;"><?php _e( 'Must be a approved sender id associated with your iletimerkezi.com account', ILT_TD ); ?></span></th>
				<td>
					<input size="50" type="text" name="<?php echo ILT_CORE_OPTION; ?>[sender]" placeholder="ILT MRKZI" value="<?php echo htmlspecialchars( $options['sender'] ); ?>" class="regular-text" />
					<br />
					<small><?php _e( 'Sender Id', ILT_TD ); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Advanced &amp; Debug Options', ILT_TD ); ?><br /><span style="font-size: x-small;"><?php _e( 'Easly debug.', ILT_TD ); ?></span></th>
				<td>
					<label><input type="checkbox" name="<?php echo ILT_CORE_OPTION; ?>[logging]" value="1" <?php checked( $options['logging'], '1', true ); ?> /> <?php _e( 'Enable Logging', ILT_TD ); ?></label><br />
					<small><?php _e( 'Enable or Disable Logging', ILT_TD ); ?></small><br /><br />
					<label><input type="checkbox" name="<?php echo ILT_CORE_OPTION; ?>[mobile_field]" value="1" <?php checked( $options['mobile_field'], '1', true ); ?> /> <?php _e( 'Add Mobile Number Field to User Profiles', ILT_TD ); ?></label><br />
					<small><?php _e( 'Adds a new field "Mobile Number" under Contact Info on all user profile forms.', ILT_TD ); ?></small><br /><br />
					<label><input type="checkbox" name="<?php echo ILT_CORE_OPTION; ?>[url_shorten]" value="1" class="url-shorten-checkbox" <?php checked( $options['url_shorten'], '1', true ); ?> /> <?php _e( 'Shorten URLs using Google', ILT_TD ); ?></label><br />
					<input size="50" type="text" name="<?php echo ILT_CORE_OPTION; ?>[url_shorten_api_key]" placeholder="<?php _e( 'Enter Google Project API key', ILT_TD ); ?>" value="<?php echo htmlspecialchars( $options['url_shorten_api_key'] ); ?>" class="regular-text url-shorten-key-text" style="display:block;" />
					<small><?php _e( 'Shorten all URLs in the message using the <a href="https://code.google.com/apis/console/" target="_blank">Google URL Shortener API</a>. Checking will display the API key field.', ILT_TD ); ?></small><br />
				</td>
			</tr>
		</table>
		<?php settings_fields( ILT_CORE_SETTING ); ?>
		<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', ILT_TD ) ?>" />
	</form>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			ilt_toggle_fields($);
			$('input.url-shorten-checkbox').click(function() {
				ilt_toggle_fields($);
			});
		});
		function ilt_toggle_fields($) {
			if($('input.url-shorten-checkbox').is(':checked')) {
				$('input.url-shorten-key-text').show();
			} else {
				$('input.url-shorten-key-text').hide();
			}
		}
	</script>
	<?php
}
add_action( 'ilt_display_tab', 'ilt_display_tab_general', 10, 2 );

/**
 * Display the Test SMS tab
 * @return void
 */
function ilt_display_tab_test( $tab, $page_url ) {
	if( $tab != 'test' ) {
		return;
	}

	$number_to = $message = '';

	if( isset( $_POST['submit'] ) ) {
		check_admin_referer( 'ilt-test' );
		if( !$_POST['number_to'] || !$_POST['message'] ) {
			printf( '<div class="error"> <p> %s </p> </div>', esc_html__( 'Some details are missing. Please fill all the fields below and try again.', ILT_TD ) );
			extract( $_POST );
		} else {
			$response = ilt_send_sms( stripslashes_deep( $_POST ) );
			if( is_wp_error( $response ) ) {
				printf( '<div class="error"> <p> %s </p> </div>', esc_html( $response->get_error_message() ) );
				extract( $_POST );
			} else {
				printf( '<div class="updated settings-error notice is-dismissible"> <p> Successfully Sent! Message ID: <strong>%s</strong> </p> </div>', esc_html( $response ) );
			}
		}
	}
	?>
	<h3><?php _e( 'Send a Message', ILT_TD ); ?></h3>
	<p><?php _e( 'If you are sending messages while in trial mode, you are only receive test message text.', ILT_TD ); ?></p>
	<form method="post" action="<?php echo esc_url( add_query_arg( array( 'tab' => $tab ), $page_url ) ); ?>">
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Recipient Number', ILT_TD ); ?></th>
				<td>
					<input size="50" type="text" name="number_to" placeholder="+905909009090" value="<?php echo $number_to; ?>" class="regular-text" />
					<br />
					<small><?php _e( 'The destination phone number. Format with a \'+\' and country code e.g., +905909009090 ', ILT_TD ); ?></small>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Message Body', ILT_TD ); ?><br /><span style="font-size: x-small;">
				<td>
					<textarea name="message" maxlength="1600" class="large-text" rows="7"><?php echo $message; ?></textarea>
					<small><?php _e( 'The text of the message you want to send, limited to 1045 characters.', ILT_TD ); ?></small><br />
				</td>
			</tr>
		</table>
		<?php wp_nonce_field( 'ilt-test' ); ?>
		<input name="submit" type="submit" class="button-primary" value="<?php _e( 'Send Message', ILT_TD ) ?>" />
	</form>
	<?php
}
add_action( 'ilt_display_tab', 'ilt_display_tab_test', 10, 2 );

/**
 * Display the Logs tab
 * @return void
 */
function ilt_display_logs( $tab, $page_url ) {
	if( $tab != 'logs' ) {
		return;
	}
	if ( isset( $_GET['clear_logs'] ) && $_GET['clear_logs'] == '1' ) {
		check_admin_referer( 'clear_logs' );
		update_option( ILT_LOGS_OPTION, '' );
		$logs_cleared = true;
	}

	if ( isset( $logs_cleared ) && $logs_cleared ) { ?>
		<div id="setting-error-settings_updated" class="updated settings-error"><p><strong><?php _e( 'Logs Cleared', ILT_TD ); ?></strong></p></div>
	<?php
	}

	$options = ilt_get_options();
	if ( !$options['logging'] ) {
		printf( '<div class="error"> <p> %s </p> </div>', esc_html__( 'Logging currently disabled.', ILT_TD ) );
	}
	$clear_log_url = esc_url( wp_nonce_url( add_query_arg( array( 'tab' => $tab, 'clear_logs' => 1 ), $page_url ), 'clear_logs' ) );
	?>
	<p><a class="button gray" href="<?php echo $clear_log_url; ?>"><?php _e( 'Clear Logs', ILT_TD ); ?></a></p>
	<h3><?php _e( 'Logs', ILT_TD ); ?></h3>
<pre>
<?php echo get_option( ILT_LOGS_OPTION ); ?>
</pre>
	<?php
}
add_action( 'ilt_display_tab', 'ilt_display_logs', 10, 2 );