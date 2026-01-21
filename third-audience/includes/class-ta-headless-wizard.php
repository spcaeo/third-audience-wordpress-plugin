<?php
/**
 * Headless Wizard - Configuration assistant for headless WordPress setups.
 *
 * Provides auto-detection and configuration generation for headless WordPress
 * setups using Next.js, Nuxt, Gatsby, or other frontend frameworks.
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
 * Handles headless WordPress detection and configuration.
 *
 * @since 1.1.0
 */
class TA_Headless_Wizard {

	/**
	 * Option key for headless settings.
	 *
	 * @var string
	 */
	const SETTINGS_OPTION = 'ta_headless_settings';

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Security instance.
	 *
	 * @var TA_Security
	 */
	private $security;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		$this->logger   = TA_Logger::get_instance();
		$this->security = TA_Security::get_instance();
	}

	/**
	 * Detect if WordPress is running in headless mode.
	 *
	 * @since 1.1.0
	 * @return array Detection results.
	 */
	public function detect_headless_mode() {
		$indicators = array();

		// Check 1: REST API usage vs HTML requests.
		$rest_requests = $this->get_rest_api_usage();
		$indicators['rest_api_heavy'] = $rest_requests > 0.5; // More than 50% REST API traffic.

		// Check 2: Check for common headless themes.
		$theme = wp_get_theme();
		$headless_themes = array( 'headless', 'faust', 'blank', 'atlas', 'frontity' );
		$indicators['headless_theme'] = false;
		foreach ( $headless_themes as $ht ) {
			if ( stripos( $theme->get( 'Name' ), $ht ) !== false ) {
				$indicators['headless_theme'] = true;
				break;
			}
		}

		// Check 3: Check for headless plugins.
		$headless_plugins = array(
			'wp-graphql/wp-graphql.php',
			'faustwp/faustwp.php',
			'wp-gatsby/wp-gatsby.php',
		);
		$indicators['headless_plugins'] = false;
		foreach ( $headless_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$indicators['headless_plugins'] = true;
				$indicators['detected_plugin'] = $plugin;
				break;
			}
		}

		// Check 4: Frontend URL different from WordPress URL.
		$frontend_url = get_option( 'ta_headless_frontend_url', '' );
		$wp_url = site_url();
		$indicators['separate_frontend'] = ! empty( $frontend_url ) && $frontend_url !== $wp_url;

		// Overall detection.
		$is_headless = $indicators['rest_api_heavy'] || $indicators['headless_theme'] ||
		               $indicators['headless_plugins'] || $indicators['separate_frontend'];

		return array(
			'is_headless' => $is_headless,
			'confidence'  => $this->calculate_confidence( $indicators ),
			'indicators'  => $indicators,
		);
	}

	/**
	 * Get REST API usage percentage.
	 *
	 * @since 1.1.0
	 * @return float Percentage of REST API requests (0.0 to 1.0).
	 */
	private function get_rest_api_usage() {
		// Check for REST API requests in the last 24 hours.
		// This is a simplified check - in production, you'd track this via analytics.
		global $wpdb;

		// For now, return a default based on common headless setups.
		// In a real implementation, you'd track actual request patterns.
		return 0.0;
	}

	/**
	 * Calculate confidence level for headless detection.
	 *
	 * @since 1.1.0
	 * @param array $indicators Detection indicators.
	 * @return string Confidence level (high, medium, low).
	 */
	private function calculate_confidence( $indicators ) {
		$score = 0;

		if ( $indicators['rest_api_heavy'] ) {
			$score += 40;
		}
		if ( $indicators['headless_theme'] ) {
			$score += 30;
		}
		if ( $indicators['headless_plugins'] ) {
			$score += 20;
		}
		if ( $indicators['separate_frontend'] ) {
			$score += 10;
		}

		if ( $score >= 50 ) {
			return 'high';
		} elseif ( $score >= 30 ) {
			return 'medium';
		} else {
			return 'low';
		}
	}

	/**
	 * Generate configuration snippets for different server types.
	 *
	 * @since 1.1.0
	 * @param string $server_type Server type (nginx, apache, cloudflare, vercel).
	 * @param array  $config      Configuration parameters.
	 * @return string Configuration snippet.
	 */
	public function generate_server_config( $server_type, $config = array() ) {
		$defaults = array(
			'wp_backend_url'  => site_url(),
			'frontend_url'    => get_option( 'ta_headless_frontend_url', '' ),
			'wp_backend_port' => '8080',
			'frontend_port'   => '3000',
		);

		$config = wp_parse_args( $config, $defaults );

		switch ( $server_type ) {
			case 'nginx':
				return $this->generate_nginx_config( $config );

			case 'apache':
				return $this->generate_apache_config( $config );

			case 'cloudflare':
				return $this->generate_cloudflare_config( $config );

			case 'vercel':
				return $this->generate_vercel_config( $config );

			default:
				return '';
		}
	}

	/**
	 * Generate Nginx configuration snippet.
	 *
	 * @since 1.1.0
	 * @param array $config Configuration parameters.
	 * @return string Nginx configuration.
	 */
	private function generate_nginx_config( $config ) {
		$wp_backend = parse_url( $config['wp_backend_url'] );
		$wp_host = $wp_backend['host'] ?? 'localhost';
		$wp_port = $config['wp_backend_port'];

		return <<<NGINX
# Nginx Configuration for Third Audience (Headless WordPress)
# Add this to your Nginx server block

# Route .md requests to WordPress backend
location ~* \.md$ {
    proxy_pass http://{$wp_host}:{$wp_port};
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto \$scheme;
}

# WordPress REST API endpoints (for Third Audience)
location ~ ^/wp-json/third-audience/ {
    proxy_pass http://{$wp_host}:{$wp_port};
    proxy_set_header Host \$host;
    proxy_set_header X-Real-IP \$remote_addr;
}

# All other requests go to Next.js frontend
location / {
    proxy_pass http://localhost:{$config['frontend_port']};
    proxy_http_version 1.1;
    proxy_set_header Upgrade \$http_upgrade;
    proxy_set_header Connection 'upgrade';
    proxy_set_header Host \$host;
    proxy_cache_bypass \$http_upgrade;
}

# After adding this config:
# 1. Test configuration: sudo nginx -t
# 2. Reload Nginx: sudo systemctl reload nginx
NGINX;
	}

	/**
	 * Generate Apache configuration snippet.
	 *
	 * @since 1.1.0
	 * @param array $config Configuration parameters.
	 * @return string Apache configuration.
	 */
	private function generate_apache_config( $config ) {
		$wp_backend = $config['wp_backend_url'];

		return <<<APACHE
# Apache Configuration for Third Audience (Headless WordPress)
# Add this to your .htaccess or VirtualHost configuration

<IfModule mod_rewrite.c>
    RewriteEngine On

    # Route .md requests to WordPress backend
    RewriteCond %{REQUEST_URI} \.md$
    RewriteRule ^(.*)$ {$wp_backend}/\$1 [P,L]

    # WordPress REST API for Third Audience
    RewriteCond %{REQUEST_URI} ^/wp-json/third-audience/
    RewriteRule ^(.*)$ {$wp_backend}/\$1 [P,L]

    # All other requests to Next.js frontend
    RewriteCond %{REQUEST_URI} !\.md$
    RewriteCond %{REQUEST_URI} !^/wp-json/third-audience/
    RewriteRule ^(.*)$ http://localhost:{$config['frontend_port']}/\$1 [P,L]
</IfModule>

# Enable proxy modules:
# sudo a2enmod proxy proxy_http rewrite
# sudo systemctl restart apache2
APACHE;
	}

	/**
	 * Generate Cloudflare Workers configuration.
	 *
	 * @since 1.1.0
	 * @param array $config Configuration parameters.
	 * @return string Cloudflare Workers script.
	 */
	private function generate_cloudflare_config( $config ) {
		$wp_backend = $config['wp_backend_url'];
		$frontend = $config['frontend_url'];

		return <<<CLOUDFLARE
// Cloudflare Worker for Third Audience (Headless WordPress)
// Deploy this at workers.cloudflare.com

addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})

async function handleRequest(request) {
  const url = new URL(request.url)

  // Route .md requests to WordPress
  if (url.pathname.endsWith('.md') || url.pathname.startsWith('/wp-json/third-audience/')) {
    const wpUrl = '{$wp_backend}' + url.pathname + url.search
    return fetch(wpUrl, {
      method: request.method,
      headers: request.headers,
      body: request.body
    })
  }

  // All other requests to frontend
  const frontendUrl = '{$frontend}' + url.pathname + url.search
  return fetch(frontendUrl, request)
}

// Deploy: wrangler publish
CLOUDFLARE;
	}

	/**
	 * Generate Vercel configuration.
	 *
	 * @since 1.1.0
	 * @param array $config Configuration parameters.
	 * @return string Vercel rewrites configuration (vercel.json).
	 */
	private function generate_vercel_config( $config ) {
		$wp_backend = $config['wp_backend_url'];

		return <<<VERCEL
{
  "rewrites": [
    {
      "source": "/:path*.md",
      "destination": "{$wp_backend}/:path*.md"
    },
    {
      "source": "/wp-json/third-audience/:path*",
      "destination": "{$wp_backend}/wp-json/third-audience/:path*"
    }
  ]
}

// Add this to your vercel.json file in the root of your Next.js project
// Then redeploy: vercel --prod
VERCEL;
	}

	/**
	 * Generate Next.js configuration snippet.
	 *
	 * @since 1.1.0
	 * @param array $config Configuration parameters.
	 * @return string Next.js configuration.
	 */
	public function generate_nextjs_config( $config = array() ) {
		$defaults = array(
			'wp_backend_url' => site_url(),
		);

		$config = wp_parse_args( $config, $defaults );
		$wp_backend = $config['wp_backend_url'];

		return <<<NEXTJS
// next.config.js
// Add this to your Next.js configuration

module.exports = {
  async rewrites() {
    return [
      // Route .md requests to WordPress backend
      {
        source: '/:path*.md',
        destination: '{$wp_backend}/:path*.md',
      },
      // Route Third Audience REST API
      {
        source: '/wp-json/third-audience/:path*',
        destination: '{$wp_backend}/wp-json/third-audience/:path*',
      },
    ]
  },
}

// After updating next.config.js:
// 1. Restart your dev server: npm run dev
// 2. For production: npm run build && npm start
NEXTJS;
	}

	/**
	 * Get headless settings.
	 *
	 * @since 1.1.0
	 * @return array Headless settings.
	 */
	public function get_settings() {
		return get_option( self::SETTINGS_OPTION, array(
			'enabled'          => false,
			'frontend_url'     => '',
			'server_type'      => 'nginx',
			'framework'        => 'nextjs',
			'auto_detected'    => false,
			'last_configured'  => '',
		) );
	}

	/**
	 * Save headless settings.
	 *
	 * @since 1.1.0
	 * @param array $settings Settings to save.
	 * @return bool Whether settings were saved.
	 */
	public function save_settings( $settings ) {
		$sanitized = array(
			'enabled'         => ! empty( $settings['enabled'] ),
			'frontend_url'    => esc_url_raw( $settings['frontend_url'] ?? '' ),
			'server_type'     => $this->security->sanitize_text( $settings['server_type'] ?? 'nginx' ),
			'framework'       => $this->security->sanitize_text( $settings['framework'] ?? 'nextjs' ),
			'auto_detected'   => ! empty( $settings['auto_detected'] ),
			'last_configured' => current_time( 'mysql' ),
		);

		// Store frontend URL in options for easy access.
		update_option( 'ta_headless_frontend_url', $sanitized['frontend_url'], false );

		$result = update_option( self::SETTINGS_OPTION, $sanitized, false );

		if ( $result ) {
			$this->logger->info( 'Headless settings updated.', $sanitized );
		}

		return $result;
	}

	/**
	 * Test headless configuration.
	 *
	 * @since 1.1.0
	 * @return array Test results.
	 */
	public function test_configuration() {
		$settings = $this->get_settings();
		$results = array(
			'success' => false,
			'tests'   => array(),
		);

		// Test 1: Check if frontend URL is accessible.
		if ( ! empty( $settings['frontend_url'] ) ) {
			$response = wp_remote_get( $settings['frontend_url'], array( 'timeout' => 10 ) );
			$results['tests']['frontend_accessible'] = array(
				'label'   => 'Frontend URL Accessible',
				'status'  => ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200,
				'message' => is_wp_error( $response ) ? $response->get_error_message() : 'Frontend is accessible',
			);
		}

		// Test 2: Create a test post and try to access .md version.
		$test_post_id = wp_insert_post( array(
			'post_title'   => 'Third Audience Test Post',
			'post_content' => 'This is a test post for headless configuration.',
			'post_status'  => 'publish',
			'post_type'    => 'post',
		) );

		if ( $test_post_id ) {
			$permalink = get_permalink( $test_post_id );
			$md_url = $permalink . '.md';

			$response = wp_remote_get( $md_url, array( 'timeout' => 10 ) );
			$results['tests']['md_url_works'] = array(
				'label'   => '.md URL Conversion Works',
				'status'  => ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200,
				'message' => is_wp_error( $response ) ? $response->get_error_message() : 'Markdown conversion is working',
				'test_url' => $md_url,
			);

			// Clean up test post.
			wp_delete_post( $test_post_id, true );
		}

		// Overall success if all tests pass.
		$all_passed = true;
		foreach ( $results['tests'] as $test ) {
			if ( ! $test['status'] ) {
				$all_passed = false;
				break;
			}
		}

		$results['success'] = $all_passed;

		return $results;
	}
}
