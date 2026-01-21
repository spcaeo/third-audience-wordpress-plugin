<?php
/**
 * Simple webhook test endpoint for Third Audience plugin.
 *
 * Save this file to your web server (outside WordPress root ideally)
 * or use a service like webhook.site to test webhook delivery.
 *
 * Usage:
 * 1. Deploy this file to your server: https://example.com/webhook-test.php
 * 2. Go to Third Audience Settings > Webhooks
 * 3. Set Webhook URL to: https://example.com/webhook-test.php
 * 4. Click "Send Test Webhook"
 * 5. Check the output below to see if payload was received
 *
 * This file logs all webhook requests to a text file.
 */

// Set headers
header( 'Content-Type: application/json' );
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Methods: POST' );

// Verify origin
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
if ( stripos( $user_agent, 'Third Audience' ) === false ) {
	http_response_code( 403 );
	echo json_encode( array(
		'error'   => 'Invalid request source',
		'message' => 'User-Agent header does not contain "Third Audience"',
		'received_ua' => $user_agent,
	) );
	exit;
}

// Get request body
$input = file_get_contents( 'php://input' );
$payload = json_decode( $input, true );

// Prepare response
$response = array(
	'success'     => true,
	'message'     => 'Webhook received successfully',
	'event'       => $payload['event'] ?? 'unknown',
	'timestamp'   => date( 'Y-m-d H:i:s' ),
	'received_at' => $payload['timestamp'] ?? null,
);

// Log to file
$log_file = __DIR__ . '/webhook-requests.log';
$log_entry = json_encode( array(
	'received_at'    => date( 'Y-m-d H:i:s' ),
	'event'          => $payload['event'] ?? 'unknown',
	'site_url'       => $payload['site_url'] ?? 'unknown',
	'user_agent'     => $user_agent,
	'payload_size'   => strlen( $input ),
	'http_code'      => 200,
) ) . "\n";

// Append to log file
if ( is_writable( dirname( $log_file ) ) ) {
	file_put_contents( $log_file, $log_entry, FILE_APPEND );
}

// Send response
http_response_code( 200 );
echo json_encode( $response, JSON_PRETTY_PRINT );

// Also output for debugging
echo "\n\n<!-- Webhook Payload Received -->\n";
echo "<!-- Event: " . ( $payload['event'] ?? 'unknown' ) . " -->\n";
if ( isset( $payload['data'] ) ) {
	echo "<!-- Data: " . json_encode( $payload['data'] ) . " -->\n";
}
?>
