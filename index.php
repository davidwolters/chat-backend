<?php
header( "Content-Type: application/json; charset=UTF-8" );
header( "Access-Control-Allow-Methods: POST" );
header( "Access-Control-Max-Age: 3600" );
header( "Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With" );

require_once 'db/Database.php';
require_once 'includes/functions.php';
require_once 'auth/auth-functions.php';
require_once 'objects/User.php';


$db = new Database();
parse_request();

function parse_request() {
	global $db;
	if ( ! $db->connect() ) {
		echo API_error( 'Could not connect to database' );
	} else {
		if ( ! isset( $_POST['action'] ) ) {
			echo API_error( 'No action provided' );
			return;
		}

		switch ($_POST['action']) {
			case 'register':
				include 'api/register.php';
				break;
			case 'login':
				include 'api/login.php';
				break;
			case 'user-exists':
				include 'api/user-exists.php';
				break;
			case 'send-friend-request':
				include 'api/send-friend-request.php';
				break;
			case 'accept-friend-request':
				include 'api/accept-friend-request.php';
				break;
			case 'get-friends':
				include 'api/get-friend-data.php';
				break;
			case 'send-message':
				include 'api/send-message.php';
				break;
			case 'get-conversation':
				include 'api/get-conversation.php';
				break;
		}
	}
}