<?php

header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

function user_exists() {
	global $db;
	if ( ! isset( $_POST['username'] ) ) {
		echo API_error( 'No username provided.' );

		return;
	}


	$user           = new User( $db->getConnection() );
	$user->username = $_POST['username'];
	$user_exists    = $user->user_exists();
	if ( $user_exists ) {
		$user->load();
		echo API_success( array(
			'user_exists' => true,
			'user'        => $user->to_object()
		) );

		return;
	}
	echo API_success( array(
		'user_exists' => false,

	) );

}
user_exists();