<?php

require_once( 'db-config.php' );

class Database {

	private $connection;

	public function connect() {
		$this->connection = null;

		try {
			$this->connection = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
		} catch(PDOException $exception){
			return false;
		}

		return $this->connection;
	}

	public function getConnection() {
		return $this->connection;
	}
}