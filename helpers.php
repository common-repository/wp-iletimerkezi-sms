<?php

/**
 * Sends the actual SMS
 * @param  array $args Array of arguments described here: https://www.toplusmsapi.com
 * @return array Response object from Iletimerkezi PHP library on success or WP_Error object on failure
 */
function ilt_send_sms( $args ) {
	$options              = ilt_get_options();
	$options['number_to'] = $options['message'] = '';
	$args                 = wp_parse_args( $args, $options );
	$is_args_valid        = ilt_validate_sms_args( $args );

	if( !$is_args_valid ) {
		extract( $args );

		$message = apply_filters( 'ilt_sms_message', $message, $args );

		try {
			$client = Emarka\Sms\Client::createClient([
			    'api_key'        => $args['public_key'],
			    'secret'         => $args['private_key'],
			    'sender'         => $args['sender'],
			]);

			$response = $client->send( $number_to, $message );

			if(!$response) {
				$is_args_valid = ilt_log_entry_format( __( '[Api Error] Connection error', ILT_TD ), $args );
				$return        = new WP_Error( 'api-error', __( '[Api Error] Connection error', ILT_TD ), $e );
			} else {
				$is_args_valid = ilt_log_entry_format( sprintf( __( 'Success! Message ID: %s', ILT_TD ), $response), $args );
				$return        = $response;
			}


		} catch( \Exception $e ) {
			$is_args_valid    = ilt_log_entry_format( sprintf( __( '[Api Error] %s ', ILT_TD ), $e->getMessage() ), $args );
			$return = new WP_Error( 'api-error', $e->getMessage(), $e );
		}


	} else {
		$return = new WP_Error( 'missing-details', __( 'Some details are missing. Please make sure you have added all details in the settings tab.', ILT_TD ) );
	}

	ilt_update_logs( $is_args_valid, $args['logging'] );
	return $return;
}

/**
 * Update logs primarily from ilt_send_sms() function
 * @param  string $log String of new-line separated log entries to be added
 * @param  int/boolean $enabled Whether to update logs or skip
 * @return void
 */
function ilt_update_logs( $log, $enabled = 1 ) {
	$options = ilt_get_options();
	if ( $enabled == 1 ) {
		$current_logs = get_option( ILT_LOGS_OPTION );
		$new_logs     = $log . $current_logs;
		$logs_array   = explode( "\n", $new_logs );

		if ( count( $logs_array ) > 100 ) {
			$logs_array = array_slice( $logs_array, 0, 100 );
			$new_logs   = implode( "\n", $logs_array );
		}

		update_option( ILT_LOGS_OPTION, $new_logs );
	}
}

/**
 * Get saved options
 * @return array of saved options
 */
function ilt_get_options() {
	return apply_filters( 'ilt_options', get_option( ILT_CORE_OPTION, array() ) );
}

/**
 * Sanitizes option array before it gets saved
 * @param $array array of options to be saved
 * @return array of sanitized options
 */
function ilt_sanitize_option( $option ) {
	$keys = array_keys( ilt_get_defaults() );
	foreach( $keys as $key ) {
		if( !isset( $option[$key] ) ) {
			$option[$key] = '';
		}
	}
	return $option;
}

/**
 * Get default option array
 * @return array of default options
 */
function ilt_get_defaults() {
	$ilt_defaults = array(
		'sender'              => '',
		'public_key'          => '',
		'private_key'         => '',
		'logging'             => '',
		'mobile_field'        => '',
		'url_shorten'         => '',
		'url_shorten_api_key' => '',
	);
	return apply_filters( 'ilt_defaults', $ilt_defaults );
}

/**
 * Format log message with more information
 * @param  string $message Message to be formatted
 * @param  array $args Send message arguments
 * @return string Formatted message entry
 */
function ilt_log_entry_format( $message = '', $args ) {
	if ( $message == '' )
		return $message;

	return date( 'Y-m-d H:i:s' ) . ' -- ' . __( 'From: ', ILT_TD ) . $args['sender'] . ' -- ' . __( 'To: ', ILT_TD ) . $args['number_to'] . ' -- ' . $message . "\n";
}

/**
 * Validates args before sending message
 * @param  array $args Send message arguments
 * @return string Log entries for invalid arguments
 */
function ilt_validate_sms_args( $args ) {

	$log = '';

	if( !$args['sender'] ) {
		$log .= ilt_log_entry_format( __( '[Argument missing] Iletimerkezi Sender ID', ILT_TD ), $args );
	}

	if( !$args['number_to'] ) {
		$log .= ilt_log_entry_format( __( '[Argument missing] Recipient Number', ILT_TD ), $args );
	}

	if( !$args['message'] ) {
		$log .= ilt_log_entry_format( __( '[Argument missing] Message', ILT_TD ), $args );
	}

	if( !$args['public_key'] ) {
		$log .= ilt_log_entry_format( __( '[Argument missing] Public Key', ILT_TD ), $args );
	}

	if( !$args['private_key'] ) {
		$log .= ilt_log_entry_format( __( '[Argument missing] Private Key', ILT_TD ), $args );
	}

	return $log;
}

/**
 * Saves the User Profile Settings
 * @param  int $user_id The User ID being saved
 * @return void         Saves to Usermeta
 */
function ilt_save_profile_settings( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) )
		return false;

	$user_key = sanitize_text_field( $_POST['mobile_number'] );
	update_user_meta( $user_id, 'mobile_number', $_POST['mobile_number'] );
}

/**
 * Add the Mobile Number field to the Profile page
 * @param  array $contact_methods List of contact methods
 * @return array The list of contact methods with the mobile field added
 */
function ilt_add_contact_item( $contact_methods ) {
	$contact_methods['mobile_number'] = __( 'Mobile Number', ILT_TD );

	return $contact_methods;
}