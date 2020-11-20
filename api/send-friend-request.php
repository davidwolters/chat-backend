<?php
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );


function send_friend_request() {

	if ( ! API_valid_fields( $_POST['username'], $_POST['password'], $_POST['recipient'] ) ) {
		echo API_error( 'Insufficient data' );

		return;
	}


	$user = auth_user_login( $_POST['username'], $_POST['password'] );

	if ( false === $user ) {
		echo API_error( 'Invalid credentials' );
		return;
	}

	$user->load();

	if ( ! $user->send_friend_request( intval( $_POST[ 'recipient' ] ) ) ) {
		echo API_error( 'Friend request could not be sent' );
		return;
	}

	echo API_success(null);
}

send_friend_request();