<?php

header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );



function login() {

	if ( ! isset( $_POST['username'] ) or ! isset( $_POST['password'] ) ) {
		echo API_error( 'Please provide both a username and a password.' );
		return;
	}

	$username = $_POST['username'];
	$password = $_POST['password'];
	$user = auth_user_login( $username, $password );
	if ( false === $user ) {
		echo API_error( 'Incorrect credentials.' );
		return;
	}

	if ( !$user->load() ) {
		echo API_error( 'Could not load user' );
		return;
	}


	echo API_success( $user->to_object() );

}
login();