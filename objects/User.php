<?php

class User {
	public $ID;
	public $username;
	public $password;

	public $friends = array();
	public $friend_requests_pending = array();
	public $friend_requests_sent = array();
	public $meta_fields = array();

	private $connection;


	public function __construct( $connection ) {
		$this->connection = $connection;
	}

	public function user_exists() {
		$query = "SELECT * FROM users WHERE username = :username";

		$stmt = $this->connection->prepare( $query );

		$this->sanitize_fields();


		$stmt->bindParam( ':username', $this->username );

		$stmt->execute();
		if ( $stmt->rowCount() > 0 ) {
			$row            = $stmt->fetch( PDO::FETCH_ASSOC );
			$this->ID       = $row['ID'];
			$this->username = $row['username'];
			$this->password = $row['password'];

			return true;
		}

		return false;
	}

	public function create() {
		$query = "INSERT INTO users (username, password) VALUES (:username, :password)";

		$stmt = $this->connection->prepare( $query );

		$this->sanitize_fields();
		$hash = password_hash( $this->password, PASSWORD_DEFAULT );

		$stmt->bindParam( ':username', $this->username, PDO::PARAM_STR );
		$stmt->bindParam( ':password', $hash, PDO::PARAM_STR );


		if ( $stmt->execute() ) {
			return true;
		} else {
			return false;
		}
	}

	public function load() {
		if ( ! isset( $this->ID ) ) {
			return false;
		}
		$this->ID = intval( $this->ID );

		if ( $this->load_user_data() ) {
			$this->load_meta_data();
			$this->load_friends();

			return true;
		}

		return false;
	}

	public function send_friend_request( $recipient_ID ) {
		if ( ! isset( $this->ID ) ) {
			return false;
		}
		$recipient_ID = intval( $recipient_ID );

		// Check that we don't have a friend request from this user.
		$friend_data = $this->get_friend_data( $recipient_ID );

		if ( $friend_data->rowCount() > 0 ) {
			return false;
		}

		$query =
			"INSERT INTO friends (user_from, user_to, last_modified, status) VALUES( :ID, :recipient_ID, :last_modified, 'pending' )";

		$stmt = $this->connection->prepare( $query );

		$now = date( 'Y/m/d H:i:s' );
		$stmt->bindParam( ':ID', $this->ID );
		$stmt->bindParam( ':recipient_ID', $recipient_ID );
		$stmt->bindParam( ':last_modified', $now );

		if ( $stmt->execute() ) {
			return true;
		}

		return false;
	}

	public function accept_friend_request( $sender_ID ) {
		if ( ! isset( $this->ID ) ) {
			return false;
		}
		$sender_ID = intval( $sender_ID );

		// Check that we have a friend request from this person.
		$friend_data = $this->get_friend_data( $sender_ID );

		if ( 0 === $friend_data->rowCount() ) {

			return false;
		}

		$row    = $friend_data->fetch( PDO::FETCH_ASSOC );
		$row_ID = intval( $row['ID'] );
		if ( $sender_ID !== intval( $row['user_from'] ) ) {

			return false;
		}

		$query = "UPDATE friends SET status = 'accepted', last_modified=:now  WHERE ID = :ID";
		$stmt  = $this->connection->prepare( $query );
		$now   = date( 'Y/m/d H:i:s' );
		$stmt->bindParam( ':ID', $row_ID );
		$stmt->bindParam( ':now', $now );
		if ( ! $stmt->execute() ) {
			return false;
		}

		return true;
	}

	public function send_message( $recipient_ID, $message ) {
		if ( ! isset( $this->ID ) ) {
			return false;
		}
		$this->ID = intval( $this->ID );
		$message  = htmlspecialchars( strip_tags( $message ) );

		$query =
			"INSERT INTO messages (user_from, user_to, message, sent_time) VALUES( :from_ID, :to_ID, :message, :sent )";

		$stmt = $this->connection->prepare( $query );
		$now  = date( 'Y/m/d/ H:i:s' );
		$stmt->bindParam( ':from_ID', $this->ID );
		$stmt->bindParam( ':to_ID', $recipient_ID );
		$stmt->bindParam( ':message', $message );
		$stmt->bindParam( ':sent', $now );

		if ( ! $stmt->execute() ) {
			return false;
		} else {
			return true;
		}
	}

	public function get_conversation_with( $other_ID, $last_msg_ID ) {
		if ( ! isset( $this->ID ) ) {
			return false;
		}
		$this->ID    = intval( $this->ID );
		$other_ID    = intval( $other_ID );
		$last_msg_ID = intval( $last_msg_ID );
		$query       =
			"SELECT message, ID, user_from, user_to FROM messages WHERE ( user_from = :me_from_ID OR user_to = :me_to_ID ) AND (user_from = :other_from_ID OR user_to = :other_to_ID) AND ID > :last_ID";

		$stmt = $this->connection->prepare( $query );

		$stmt->bindParam( ':me_from_ID', $this->ID );
		$stmt->bindParam( ':me_to_ID', $this->ID );
		$stmt->bindParam( ':other_from_ID', $other_ID );
		$stmt->bindParam( ':other_to_ID', $other_ID );
		$stmt->bindParam( ':last_ID', $last_msg_ID );

		$stmt->execute();


		$messages = array();
		if ( $stmt->rowCount() > 0 ) {

			$rows = $stmt->fetchAll( PDO::FETCH_ASSOC );

			foreach ( $rows as $row ) {
				$messages[] = array(
					'from'    => intval( $row['user_from'] ),
					'to'      => intval( $row['user_to'] ),
					'message' => $row['message'],
					'ID'      => $row['ID']
				);

			}
		}

		return $messages;
	}


	private function get_friend_data( $other_ID ) {
		$query    =
			"SELECT user_from, user_to, ID FROM friends WHERE (user_from = :me_from_ID OR user_to = :me_to_ID) AND (user_from = :other_from_ID OR user_to = :other_to_ID)";
		$this->ID = intval( $this->ID );
		$other_ID = intval( $other_ID );
		$stmt     = $this->connection->prepare( $query );

		$stmt->bindParam( ':me_from_ID', $this->ID );
		$stmt->bindParam( ':me_to_ID', $this->ID );
		$stmt->bindParam( ':other_from_ID', $other_ID );
		$stmt->bindParam( ':other_to_ID', $other_ID );

		$stmt->execute();

		return $stmt;

	}

	private function load_user_data() {
		$query = "SELECT * FROM users WHERE ID = :ID";

		$stmt = $this->connection->prepare( $query );

		$stmt->bindParam( ':ID', $this->ID );

		$stmt->execute();

		if ( $stmt->rowCount() > 0 ) {
			$row            = $stmt->fetch( PDO::FETCH_ASSOC );
			$this->username = $row['username'];

			return true;
		}

		return false;
	}

	private function load_meta_data() {
		$query = "SELECT meta_key, meta_value FROM usermeta WHERE user_ID = :ID";

		$stmt = $this->connection->prepare( $query );
		$stmt->bindParam( ':ID', $this->ID );

		$stmt->execute();

		if ( $stmt->rowCount() > 0 ) {
			while ( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
				$this->meta_fields[ $row['meta_key'] ] = $row['meta_value'];
			}
		}
	}

	private function load_friends() {
		$query = "SELECT user_from, user_to, status FROM friends WHERE user_from = :from_ID OR user_to = :to_ID";

		$stmt = $this->connection->prepare( $query );
		$stmt->bindParam( ':from_ID', $this->ID );
		$stmt->bindParam( ':to_ID', $this->ID );

		$stmt->execute();

		if ( $stmt->rowCount() > 0 ) {

			foreach ( $stmt->fetchAll( PDO::FETCH_ASSOC ) as $row ) {


				if ( $row['status'] === 'accepted' ) {
					if ( intval( $row['user_from'] ) === intval( $this->ID ) ) {
						$ID     = intval( $row['user_to'] );
						$friend = $this->get_friend( $ID );
						if ( false !== $friend ) {
							$this->friends[] = array(
								'ID' => $ID,
								'username' => $this->get_friend( $ID )
							);
						}

					} else {
						$ID     = intval( $row['user_from'] );
						$friend = $this->get_friend( $ID );
						if ( false !== $friend ) {
							$this->friends[] = array(
								'ID' => $ID,
								'username' => $this->get_friend( $ID )
							);
						}
					}
				} elseif ( intval( $row['user_from'] ) === intval( $this->ID ) ) {
					$ID     = intval( $row['user_to'] );
					$friend = $this->get_friend( $ID );
					if ( false !== $friend ) {
						$this->friend_requests_sent[] = array(
							'ID' => $ID,
							'username' => $this->get_friend( $ID )
						);
					}
				} elseif ( intval( $row['user_to'] ) === intval( $this->ID ) ) {
					$ID     = intval( $row['user_from'] );
					$friend = $this->get_friend( $ID );
					if ( false !== $friend ) {
						$this->friend_requests_pending[] = array(
							'ID' => $ID,
							'username' => $this->get_friend( $ID )
						);
					}

				}
			}
		}
	}

	private function get_friend( $ID ) {
		$query = "SELECT username FROM users WHERE ID = :ID";
		$stmt  = $this->connection->prepare( $query );
		$stmt->bindParam( ':ID', $ID );
		$stmt->execute();
		if ( $stmt->rowCount() > 0 ) {
			$row = $stmt->fetch( PDO::FETCH_ASSOC );

			return $row['username'];
		}

		return false;
	}

	public function has_friend( $ID ) {
		if ( !isset( $this->ID ) ) {
			return false;
		}
		$ID = intval( $ID );
		foreach ( $this->friends as $friend ) {
			if ( $friend[ 'ID' ] === $ID ) {
				return true;
			}
		}
		return false;
	}

	public function to_object() {
		return array(
			'username'                => $this->username,
			'ID'                      => $this->ID,
			'friends'                 => $this->friends,
			'friend_requests_sent'    => $this->friend_requests_sent,
			'friend_requests_pending' => $this->friend_requests_pending,
			'meta'                    => $this->meta_fields
		);
	}

	public function sanitize_fields() {
		$this->ID       = htmlspecialchars( strip_tags( $this->ID ) );
		$this->username = htmlspecialchars( strip_tags( $this->username ) );
		$this->password = htmlspecialchars( strip_tags( $this->password ) );

	}
}