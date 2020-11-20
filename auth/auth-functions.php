<?php


function auth_user_login( $username, $password ) {
	global $db;

	$user           = new User( $db->getConnection() );
	$user->username = $username;
	$user->password = $password;
	$user->sanitize_fields();

	$query = "SELECT * FROM users WHERE username = :username";


	$stmt = $db->getConnection()->prepare( $query );

	$stmt->bindParam( ':username', $user->username );

	$stmt->execute();

	if ( $stmt->rowCount() > 0 ) {
		$row         = $stmt->fetch( PDO::FETCH_ASSOC );
		$stored_pass = $row['password'];
		if ( password_verify( $user->password, $stored_pass ) ) {
			$user->ID = intval($row['ID']);
			return $user;
		}

		return false;
	} else {
		return false;
	}

	return false;
}
