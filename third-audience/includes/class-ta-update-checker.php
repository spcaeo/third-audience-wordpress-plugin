<?php
/**
 * Update Checker - Check for plugin updates from GitHub.
 *
 * Checks for new plugin versions and displays update notifications.
 *
 * @package ThirdAudience
 * @since   2.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class TA_Update_Checker
 *
 * Handles version checking and update notifications.
 *
 * @since 2.0.0
 */
class TA_Update_Checker {

	/**
	 * GitHub repository owner.
	 *
	 * @var string
	 */
	const GITHUB_OWNER = 'spcaeo';

	/**
	 * GitHub repository name.
	 *
	 * @var string
	 */
	const GITHUB_REPO = 'third-audience-wordpress-plugin';

	/**
	 * Transient key for storing version check results.
	 *
	 * @var string
	 */
	const VERSION_CACHE_KEY = 'ta_latest_version_info';

	/**
	 * Cache duration (12 hours).
	 *
	 * @var int
	 */
	const CACHE_DURATION = 43200;

	/**
	 * Logger instance.
	 *
	 * @var TA_Logger
	 */
	private $logger;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		$this->logger = TA_Logger::get_instance();
	}

	/**
	 * Initialize update checker.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function init() {
		// Check for updates daily.
		add_action( 'admin_init', array( $this, 'check_for_updates' ) );

		// Display update notice.
		add_action( 'admin_notices', array( $this, 'display_update_notice' ) );

		// Add manual check action.
		add_action( 'admin_post_ta_check_updates', array( $this, 'manual_check' ) );
	}

	/**
	 * Check for plugin updates.
	 *
	 * Fetches the raw plugin file from GitHub main branch and parses the Version header.
	 * This provides real-time version checking without requiring GitHub Releases.
	 *
	 * @since 2.0.0
	 * @since 3.3.8 Changed to fetch raw plugin file instead of GitHub Releases API.
	 * @param bool $force Force check even if cached.
	 * @return array|false Latest version info or false on failure.
	 */
	public function check_for_updates( $force = false ) {
		// Check cache first unless forced.
		if ( ! $force ) {
			$cached = get_transient( self::VERSION_CACHE_KEY );
			if ( false !== $cached ) {
				return $cached;
			}
		}

		// Fetch raw plugin file from GitHub main branch.
		$url = sprintf(
			'https://raw.githubusercontent.com/%s/%s/main/third-audience.php',
			self::GITHUB_OWNER,
			self::GITHUB_REPO
		);

		$response = wp_remote_get( $url, array(
			'timeout' => 10,
		) );

		if ( is_wp_error( $response ) ) {
			$this->logger->error( 'Failed to check for updates.', array(
				'error' => $response->get_error_message(),
			) );
			return false;
		}

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $status_code ) {
			$this->logger->warning( 'GitHub returned non-200 status.', array( 'status' => $status_code ) );
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		// Parse Version header from plugin file.
		if ( preg_match( '/^\s*\*?\s*Version:\s*(.+)$/mi', $body, $matches ) ) {
			$latest_version = trim( $matches[1] );
		} else {
			$this->logger->warning( 'Could not parse version from GitHub plugin file.' );
			return false;
		}

		// Build version info.
		$version_info = array(
			'version'      => $latest_version,
			'download_url' => sprintf( 'https://github.com/%s/%s/archive/refs/heads/main.zip', self::GITHUB_OWNER, self::GITHUB_REPO ),
			'release_url'  => sprintf( 'https://github.com/%s/%s', self::GITHUB_OWNER, self::GITHUB_REPO ),
			'release_date' => '',
			'changelog'    => '',
			'checked_at'   => current_time( 'mysql' ),
		);

		// Cache the result.
		set_transient( self::VERSION_CACHE_KEY, $version_info, self::CACHE_DURATION );

		$this->logger->info( 'Update check completed.', array(
			'latest_version'   => $version_info['version'],
			'current_version'  => TA_VERSION,
			'update_available' => version_compare( TA_VERSION, $version_info['version'], '<' ),
		) );

		return $version_info;
	}

	/**
	 * Display update notice in admin.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function display_update_notice() {
		// Only show to admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$version_info = $this->check_for_updates();

		if ( ! $version_info ) {
			return;
		}

		// Check if update is available.
		if ( version_compare( TA_VERSION, $version_info['version'], '>=' ) ) {
			return;
		}

		// Allow dismissing the notice.
		$dismissed = get_user_meta( get_current_user_id(), 'ta_dismissed_update_' . $version_info['version'], true );
		if ( $dismissed ) {
			return;
		}

		?>
		<div class="notice notice-warning is-dismissible" data-version="<?php echo esc_attr( $version_info['version'] ); ?>">
			<p>
				<strong><?php esc_html_e( 'Third Audience Update Available!', 'third-audience' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: 1: Current version, 2: New version */
					esc_html__( 'Version %1$s → %2$s is now available.', 'third-audience' ),
					'<code>' . esc_html( TA_VERSION ) . '</code>',
					'<strong><code>' . esc_html( $version_info['version'] ) . '</code></strong>'
				);
				?>
			</p>
			<?php if ( ! empty( $version_info['changelog'] ) ) : ?>
				<p>
					<strong><?php esc_html_e( 'What\'s New:', 'third-audience' ); ?></strong>
				</p>
				<div style="max-width: 800px; max-height: 200px; overflow-y: auto; background: #f9f9f9; padding: 10px; margin: 10px 0; border-left: 3px solid #667eea;">
					<?php echo wp_kses_post( wpautop( $version_info['changelog'] ) ); ?>
				</div>
			<?php endif; ?>
			<p>
				<a href="<?php echo esc_url( $version_info['release_url'] ); ?>"
				   class="button button-primary"
				   target="_blank"
				   rel="noopener">
					<?php esc_html_e( 'View Release & Download', 'third-audience' ); ?>
				</a>
				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?action=ta_dismiss_update&version=' . $version_info['version'] ), 'ta_dismiss_update' ) ); ?>"
				   class="button">
					<?php esc_html_e( 'Dismiss', 'third-audience' ); ?>
				</a>
			</p>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('.notice[data-version]').on('click', '.notice-dismiss', function() {
				var version = $(this).closest('.notice').data('version');
				$.post(ajaxurl, {
					action: 'ta_dismiss_update_notice',
					version: version,
					_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'ta_dismiss_update' ) ); ?>'
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Handle manual update check.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function manual_check() {
		// Verify permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'third-audience' ) );
		}

		check_admin_referer( 'ta_check_updates' );

		// Force check.
		$version_info = $this->check_for_updates( true );

		if ( ! $version_info ) {
			add_settings_error(
				'ta_messages',
				'ta_update_check_failed',
				__( 'Failed to check for updates. Please try again later.', 'third-audience' ),
				'error'
			);
		} else {
			$is_latest = version_compare( TA_VERSION, $version_info['version'], '>=' );

			if ( $is_latest ) {
				add_settings_error(
					'ta_messages',
					'ta_version_latest',
					sprintf(
						/* translators: %s: Current version */
						__( 'You are running the latest version (%s).', 'third-audience' ),
						TA_VERSION
					),
					'success'
				);
			} else {
				add_settings_error(
					'ta_messages',
					'ta_update_available',
					sprintf(
						/* translators: 1: Current version, 2: New version */
						__( 'Update available: %1$s → %2$s', 'third-audience' ),
						TA_VERSION,
						$version_info['version']
					),
					'warning'
				);
			}
		}

		set_transient( 'settings_errors', get_settings_errors(), 30 );

		wp_safe_redirect( admin_url( 'admin.php?page=third-audience-system-health' ) );
		exit;
	}

	/**
	 * Get current version info.
	 *
	 * @since 2.0.0
	 * @return array Version information.
	 */
	public function get_version_info() {
		$latest = $this->check_for_updates();

		// If we can't fetch from GitHub, show current version as latest
		return array(
			'current_version'  => TA_VERSION,
			'latest_version'   => $latest['version'] ?? TA_VERSION,
			'update_available' => $latest ? version_compare( TA_VERSION, $latest['version'], '<' ) : false,
			'release_url'      => $latest['release_url'] ?? '',
			'last_checked'     => $latest['checked_at'] ?? 'Not checked yet',
		);
	}
}
