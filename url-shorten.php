<?php

/**
 * Shortens URLs in the message if the option is enabled
 * @param  string $message Message to be formatted
 * @param  array $args Send message arguments
 * @return string Message with/without short URLs
 */
function ilt_filter_message_urls( $message, $args ) {

	if( $args['url_shorten'] && $args['url_shorten_api_key'] ) {
		$regex   = '"\b(https?://\S+)"';
		$message = preg_replace_callback( $regex, function( $url ) {
			return ilt_url_shorten( $url[0] );
		}, $message );
	}

	return $message;
}
add_filter( 'ilt_sms_message', 'ilt_filter_message_urls', 9999, 2 );

/**
 * Process URL via Google URL Shortener API
 * @param  string $url URL to be shortened
 * @return string Shortened URL in http://goo.gl/xxx format
 */
function ilt_url_shorten( $url ) {
	$options = ilt_get_options();
	$result  = wp_remote_post( add_query_arg( 'key', apply_filters( 'ilt_google_api_key', $options['url_shorten_api_key'] ), 'https://www.googleapis.com/urlshortener/v1/url' ), array(
		'body'    => json_encode( array( 'longUrl' => esc_url_raw( $url ) ) ),
		'headers' => array( 'Content-Type' => 'application/json' ),
	) );

	// Return the URL if the request got an error.
	if ( is_wp_error( $result ) )
		return $url;

	$result    = json_decode( $result['body'] );
	$shortlink = $result->id;
	if ( $shortlink )
		return $shortlink;

	return $url;
}