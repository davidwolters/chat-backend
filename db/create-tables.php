<?php
require_once 'Database.php';

$db = new Database();
$db->connect();

$queries = explode( ';', "
CREATE TABLE IF NOT EXISTS users (
	ID int(11) AUTO_INCREMENT,
	username VARCHAR(255) NOT NULL,
	password VARCHAR(255) NOT NULL,
	PRIMARY KEY (ID)
);
CREATE TABLE IF NOT EXISTS usermeta (
	ID int(11) AUTO_INCREMENT,
	user_ID int(11) NOT NULL,
	meta_key VARCHAR(255) NOT NULL,
	meta_value TEXT,
	last_modified DATETIME,
	PRIMARY KEY (ID)
);
CREATE TABLE IF NOT EXISTS friends (
	ID int(11) AUTO_INCREMENT,
	user_from int(11) NOT NULL,
	user_to int(11) NOT NULL,
	last_modified DATETIME,
	status ENUM('pending', 'accepted') NOT NULL,
	PRIMARY KEY (ID)
);
CREATE TABLE IF NOT EXISTS messages (
	ID int(11) AUTO_INCREMENT,
	user_from int(11) NOT NULL,
	user_to int(11) NOT NULL,
	message TEXT NOT NULL,
	sent_time DATETIME,
	PRIMARY KEY (ID)
);
" );

echo "<pre>";
foreach ( $queries as $query ) {
	echo $query;
	$stmt = $db->getConnection()->prepare( $query );
	$stmt->execute();
	var_dump( $stmt->errorInfo() );
}
echo "</pre>";