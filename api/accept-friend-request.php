<?php

header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

function accept_friend_request() {
	if ( !API_valid_fields( $_POST[ 'username' ], $_POST[ 'password' ], $_POST[ 'user' ] ) ) {
		echo API_error( 'Invalid parameters' );
		return;
	}

	$user = auth_user_login( $_POST[ 'username' ], $_POST[ 'password' ] );
	if ( false === $user ) {
		echo API_error( 'Invalid credentials' );
		return;
	}

	$user->load();
	if ( !$user->accept_friend_request( intval( $_POST[ 'user' ] ) ) ) {
		echo API_error( 'Could not accept friend request' );
		return;
	}
	echo API_success( null );
}

accept_friend_request();