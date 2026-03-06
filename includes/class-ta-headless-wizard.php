<?php
/**
 * Headless Setup Wizard
 *
 * Manages headless WordPress configuration for Third Audience.
 * Provides API key generation, code snippets, and setup guidance
 * for Next.js, Gatsby, and other headless frameworks.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Headless_Wizard
 *
 * Wizard for configuring headless WordPress integration.
 *
 * @since 1.1.0
 */
class TA_Headless_Wizard {

	/**
	 * Option name for headless settings.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'ta_headless_settings';

	/**
	 * Option name for API key.
	 *
	 * @var string
	 */
	const API_KEY_OPTION = 'ta_headless_api_key';

	/**
	 * Get current headless settings.
	 *
	 * @since 1.1.0
	 * @return array Settings array.
	 */
	public function get_settings() {
		$defaults = array(
			'enabled'      => false,
			'frontend_url' => '',
			'framework'    => 'nextjs',
			'server_type'  => 'nginx',
		);

		$settings = get_option( self::SETTINGS_OPTION, array() );

		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Save headless settings.
	 *
	 * @since 1.1.0
	 * @param array $settings Settings to save.
	 * @return bool Success.
	 */
	public function save_settings( $settings ) {
		$current = $this->get_settings();
		$updated = wp_parse_args( $settings, $current );

		// Generate API key if enabling for the first time.
		if ( $updated['enabled'] && ! $this->get_api_key() ) {
			$this->generate_api_key();
		}

		return update_option( self::SETTINGS_OPTION, $updated );
	}

	/**
	 * Get API key.
	 *
	 * @since 1.1.0
	 * @return string|false API key or false if not set.
	 */
	public function get_api_key() {
		return get_option( self::API_KEY_OPTION, false );
	}

	/**
	 * Generate new API key.
	 *
	 * @since 1.1.0
	 * @return string Generated API key.
	 */
	public function generate_api_key() {
		$api_key = 'ta_' . wp_generate_password( 32, false );
		update_option( self::API_KEY_OPTION, $api_key );

		return $api_key;
	}

	/**
	 * Regenerate API key.
	 *
	 * @since 1.1.0
	 * @return string New API key.
	 */
	public function regenerate_api_key() {
		return $this->generate_api_key();
	}

	/**
	 * Get webhook URL for headless frontend.
	 *
	 * @since 1.1.0
	 * @return string Webhook URL.
	 */
	public function get_webhook_url() {
		return rest_url( 'third-audience/v1/webhook' );
	}

	/**
	 * Get code snippet for Next.js integration.
	 *
	 * @since 1.1.0
	 * @param string $frontend_url Frontend URL.
	 * @return string Code snippet.
	 */
	public function get_nextjs_snippet( $frontend_url ) {
		$wordpress_url = home_url();
		$api_key       = $this->get_api_key();

		$snippet = <<<JS
// .env.local
WORDPRESS_URL={$wordpress_url}
THIRD_AUDIENCE_API_KEY={$api_key}

// lib/third-audience.js
export async function getMarkdown(postId) {
  const response = await fetch(
    `\${process.env.WORDPRESS_URL}/wp-json/third-audience/v1/markdown/\${postId}`,
    {
      headers: {
        'X-Third-Audience-Key': process.env.THIRD_AUDIENCE_API_KEY,
      },
    }
  );

  if (!response.ok) {
    throw new Error('Failed to fetch markdown');
  }

  return await response.json();
}
JS;

		return $snippet;
	}

	/**
	 * Get CORS configuration snippet.
	 *
	 * @since 1.1.0
	 * @param string $server_type Server type (nginx, apache).
	 * @param string $frontend_url Frontend URL.
	 * @return string CORS configuration.
	 */
	public function get_cors_snippet( $server_type, $frontend_url ) {
		$domain = wp_parse_url( $frontend_url, PHP_URL_HOST );

		if ( 'nginx' === $server_type ) {
			return <<<NGINX
# Nginx CORS configuration
location ~* ^/wp-json/third-audience/ {
    add_header 'Access-Control-Allow-Origin' 'https://{$domain}' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'X-Third-Audience-Key, Content-Type' always;
    add_header 'Access-Control-Max-Age' 1728000;

    if (\$request_method = 'OPTIONS') {
        return 204;
    }
}
NGINX;
		} elseif ( 'apache' === $server_type ) {
			return <<<APACHE
# Apache CORS configuration (.htaccess)
<IfModule mod_headers.c>
    <FilesMatch "\.(php)$">
        SetEnvIf Origin "^https://{$domain}$" AccessControlAllowOrigin=\$0
        Header always set Access-Control-Allow-Origin %{AccessControlAllowOrigin}e env=AccessControlAllowOrigin
        Header always set Access-Control-Allow-Methods "GET, POST, OPTIONS"
        Header always set Access-Control-Allow-Headers "X-Third-Audience-Key, Content-Type"
        Header always set Access-Control-Max-Age "1728000"
    </FilesMatch>
</IfModule>
APACHE;
		}

		return '';
	}

	/**
	 * Test connection from frontend.
	 *
	 * @since 1.1.0
	 * @param string $frontend_url Frontend URL.
	 * @return array Test result with success status and message.
	 */
	public function test_connection( $frontend_url ) {
		$wordpress_url = home_url();
		$api_key       = $this->get_api_key();

		// Make test request to frontend.
		$response = wp_remote_post(
			trailingslashit( $frontend_url ) . 'api/test-wordpress',
			array(
				'body'    => wp_json_encode(
					array(
						'wordpress_url' => $wordpress_url,
						'api_key'       => $api_key,
					)
				),
				'headers' => array( 'Content-Type' => 'application/json' ),
				'timeout' => 10,
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => 'Connection failed: ' . $response->get_error_message(),
			);
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( 200 === $status_code ) {
			return array(
				'success' => true,
				'message' => 'Connection successful! Your headless frontend can communicate with WordPress.',
			);
		}

		return array(
			'success' => false,
			'message' => 'Connection failed with status code: ' . $status_code,
		);
	}

	/**
	 * Check if headless mode is enabled.
	 *
	 * @since 1.1.0
	 * @return bool Whether headless mode is enabled.
	 */
	public function is_enabled() {
		$settings = $this->get_settings();
		return ! empty( $settings['enabled'] );
	}

	/**
	 * Validate API key from request header.
	 *
	 * @since 1.1.0
	 * @return bool Whether API key is valid.
	 */
	public function validate_api_key() {
		$provided_key = isset( $_SERVER['HTTP_X_THIRD_AUDIENCE_KEY'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_THIRD_AUDIENCE_KEY'] ) ) : '';
		$stored_key   = $this->get_api_key();

		return ! empty( $provided_key ) && hash_equals( $stored_key, $provided_key );
	}
}
