<?php


function API_response( $status, $data ) {
	return json_encode( array(
			'status' => $status,
			'data'   => $data
		) );
}

function API_success( $data ) {
	return API_response( 'success', $data );
}

function API_error( $data ) {
	return API_response( 'error', array( 'message' => $data ) );
}

function API_valid_fields( ...$fields ) {
	foreach ( $fields as $field ) {
		if ( is_numeric( $field ) and isset( $field ) ) {
			continue;
		}
		if ( ! isset( $field ) or empty( $field ) ) {
			return false;
		}
	}
	return true;
}