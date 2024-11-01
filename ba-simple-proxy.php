<?PHP
// Script: Simple PHP Proxy: Get external HTML, JSON and more!
// Derivative work by Richard Vencu - script was simplified for particular use, please refer to original script for other uses.
// *Original script version: 1.6, Last updated: 1/24/2009*
// 
// Project Home - http://benalman.com/projects/php-simple-proxy/
// GitHub       - http://github.com/cowboy/php-simple-proxy/
// Source       - http://github.com/cowboy/php-simple-proxy/raw/master/ba-simple-proxy.php
// 
// About: License
// 
// Copyright (c) 2010 "Cowboy" Ben Alman,
// Dual licensed under the MIT and GPL licenses.
// http://benalman.com/about/license/
// 
// Derivative work by Richard Vencu - script was simplified for particular use, please refer to original script for other uses.
$valid_domain = 'wikitip.info';
// ############################################################################
$url   = $_GET['url'];
$parts = parse_url( $url );
preg_match( '/[^.]+\.[^.]+$/', $parts['host'], $matches );
if ( ! $url ) {

	// Passed url not specified.
	$contents = 'ERROR: url not specified';
	$status   = array( 'http_code' => 'ERROR' );
} elseif ( $valid_domain != $matches[0] ) {
	// Passed url doesn't match $valid_url_regex.
	$contents = 'ERROR: invalid url: ' . $matches[0];
	$status   = array( 'http_code' => 'ERROR' );
} else {
	$ch = curl_init( $url );
	if ( strtolower( $_SERVER['REQUEST_METHOD'] ) == 'post' ) {
		curl_setopt( $ch, CURLOPT_POST, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $_POST );
		//error_log( print_r( $_POST, true ) );
	}
	if ( $_GET['send_cookies'] ) {
		$cookie = array();
		foreach ( $_COOKIE as $key => $value ) {
			$cookie[] = $key . '=' . $value;
		}
		if ( $_GET['send_session'] ) {
			$cookie[] = SID;
		}
		$cookie = implode( '; ', $cookie );
		curl_setopt( $ch, CURLOPT_COOKIE, $cookie );
	}
	curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
	curl_setopt( $ch, CURLOPT_HEADER, true );
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	curl_setopt( $ch, CURLOPT_USERAGENT, $_GET['user_agent'] ? $_GET['user_agent'] : $_SERVER['HTTP_USER_AGENT'] );
	$response = curl_exec( $ch );
	//error_log( print_r( $response, true ) );
	// Then, after your curl_exec call:
	$header_size = curl_getinfo( $ch, CURLINFO_HEADER_SIZE );
	$header      = substr( $response, 0, $header_size );
	$contents    = substr( $response, $header_size );

	curl_close( $ch );
}
// Split header text into an array.
$header_text = preg_split( '/[\r\n]+/', $header );
// Propagate headers to response.
foreach ( $header_text as $header ) {
	if ( preg_match( '/^(?:Content-Type|Content-Language|Set-Cookie):/i', $header ) ) {
		header( $header );
	}
}
header( 'Content-type: application/json' );
print $_GET['callback'] . "($contents)";