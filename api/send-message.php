<?php
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

function send_message() {
	if ( ! API_valid_fields( $_POST['username'], $_POST['password'], $_POST['recipient'], $_POST['message'] ) ) {
		echo API_error( 'Invalid parameters' );
		return;
	}

	$user = auth_user_login( $_POST[ 'username' ], $_POST[ 'password' ] );
	if ( false === $user ) {
		echo API_error( 'Invalid credentials' );
		return;
	}

	// Check that we are sending to a friend.

	$user->load();

	$recipient_ID = intval( $_POST[ 'recipient' ] );

	if ( !$user->has_friend( $recipient_ID )) {
		echo API_error( 'Can not send message to this person' );
		return;
	}

	// We are good to go.
	if ( !$user->send_message( $recipient_ID, $_POST[ 'message' ] ) ) {
		echo API_error( 'Message delivery failed' );
		return;
	}

	echo API_success(null);

}

send_message();