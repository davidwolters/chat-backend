<?php

header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );


function get_friend_data() {
	if ( ! API_valid_fields( $_POST['username'], $_POST['password'] ) ) {
		echo API_error( 'Invalid parameters' );
		return;
	}

	$user = auth_user_login( $_POST['username'], $_POST['password'] );

	if ( false === $user ) {
		echo API_error( 'Invalid credentials' );
		return;
	}

	$user->load();

	echo API_success( array(
		'friends' => $user->friends,
		'received' => $user->friend_requests_pending,
		'sent' => $user->friend_requests_sent
	) );
}

get_friend_data();