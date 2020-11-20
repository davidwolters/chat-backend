<?php

header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

function register_user() {
	global $db;

	if ( ! isset( $_POST['username'] ) or ! isset( $_POST['password'] ) ) {
		echo API_error( 'Please enter username and password.' );
		return;
	}


	$username = $_POST['username'];
	$password = $_POST['password'];


	$user           = new User( $db->getConnection() );
	$user->username = $username;
	$user->password = $password;
	if ( $user->user_exists() ) {
		echo API_error( 'That user already exists' );
		return;
	}
	if ( $user->create() ) {
		$user->user_exists();
		echo API_success( array(
			'ID' => $user->ID
		) );
		return;
	}
	echo API_error( 'User could not be created' );
}

register_user();