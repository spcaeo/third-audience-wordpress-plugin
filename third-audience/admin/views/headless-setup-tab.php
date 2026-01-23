<?php
/**
 * Headless Setup Tab
 *
 * Settings page tab for configuring headless WordPress integration.
 *
 * @package ThirdAudience
 * @since   1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Initialize wizard.
$wizard   = new TA_Headless_Wizard();
$settings = $wizard->get_settings();
$api_key  = $wizard->get_api_key();
?>

<div class="ta-settings-section">
	<h2><?php esc_html_e( 'Headless WordPress Setup', 'third-audience' ); ?></h2>
	<p class="description">
		<?php esc_html_e( 'Configure Third Audience for use with headless WordPress frameworks like Next.js, Gatsby, or Nuxt.js.', 'third-audience' ); ?>
	</p>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'save_headless_settings', 'ta_nonce' ); ?>
		<input type="hidden" name="action" value="ta_save_headless_settings" />

		<table class="form-table" role="presentation">
			<!-- Enable Headless Mode -->
			<tr>
				<th scope="row">
					<label for="ta_headless_enabled">
						<?php esc_html_e( 'Enable Headless Mode', 'third-audience' ); ?>
					</label>
				</th>
				<td>
					<label>
						<input
							type="checkbox"
							id="ta_headless_enabled"
							name="ta_headless_enabled"
							value="1"
							<?php checked( $settings['enabled'], true ); ?>
						/>
						<?php esc_html_e( 'Enable headless WordPress integration', 'third-audience' ); ?>
					</label>
					<p class="description">
						<?php esc_html_e( 'Enables API endpoints and CORS headers for headless frontend access.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>

			<!-- Frontend URL -->
			<tr>
				<th scope="row">
					<label for="ta_headless_frontend_url">
						<?php esc_html_e( 'Frontend URL', 'third-audience' ); ?>
						<span class="required">*</span>
					</label>
				</th>
				<td>
					<input
						type="url"
						id="ta_headless_frontend_url"
						name="ta_headless_frontend_url"
						value="<?php echo esc_attr( $settings['frontend_url'] ); ?>"
						class="regular-text"
						placeholder="https://example.com"
						<?php echo $settings['enabled'] ? 'required' : ''; ?>
					/>
					<p class="description">
						<?php esc_html_e( 'The URL where your headless frontend is hosted (e.g., https://example.com).', 'third-audience' ); ?>
					</p>
				</td>
			</tr>

			<!-- Framework -->
			<tr>
				<th scope="row">
					<label for="ta_headless_framework">
						<?php esc_html_e( 'Framework', 'third-audience' ); ?>
					</label>
				</th>
				<td>
					<select id="ta_headless_framework" name="ta_headless_framework">
						<option value="nextjs" <?php selected( $settings['framework'], 'nextjs' ); ?>>
							<?php esc_html_e( 'Next.js', 'third-audience' ); ?>
						</option>
						<option value="gatsby" <?php selected( $settings['framework'], 'gatsby' ); ?>>
							<?php esc_html_e( 'Gatsby', 'third-audience' ); ?>
						</option>
						<option value="nuxt" <?php selected( $settings['framework'], 'nuxt' ); ?>>
							<?php esc_html_e( 'Nuxt.js', 'third-audience' ); ?>
						</option>
						<option value="react" <?php selected( $settings['framework'], 'react' ); ?>>
							<?php esc_html_e( 'React (Custom)', 'third-audience' ); ?>
						</option>
						<option value="other" <?php selected( $settings['framework'], 'other' ); ?>>
							<?php esc_html_e( 'Other', 'third-audience' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the framework you\'re using for your headless frontend.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>

			<!-- Server Type -->
			<tr>
				<th scope="row">
					<label for="ta_headless_server_type">
						<?php esc_html_e( 'Server Type', 'third-audience' ); ?>
					</label>
				</th>
				<td>
					<select id="ta_headless_server_type" name="ta_headless_server_type">
						<option value="nginx" <?php selected( $settings['server_type'], 'nginx' ); ?>>
							<?php esc_html_e( 'Nginx', 'third-audience' ); ?>
						</option>
						<option value="apache" <?php selected( $settings['server_type'], 'apache' ); ?>>
							<?php esc_html_e( 'Apache', 'third-audience' ); ?>
						</option>
						<option value="cloudflare" <?php selected( $settings['server_type'], 'cloudflare' ); ?>>
							<?php esc_html_e( 'Cloudflare Workers', 'third-audience' ); ?>
						</option>
						<option value="vercel" <?php selected( $settings['server_type'], 'vercel' ); ?>>
							<?php esc_html_e( 'Vercel', 'third-audience' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select your server/hosting environment for CORS configuration.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button( __( 'Save Headless Settings', 'third-audience' ) ); ?>
	</form>
</div>

<?php if ( $settings['enabled'] && $api_key ) : ?>
	<!-- API Key Section -->
	<div class="ta-settings-section" style="margin-top: 30px;">
		<h2><?php esc_html_e( 'API Configuration', 'third-audience' ); ?></h2>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'API Key', 'third-audience' ); ?></label>
				</th>
				<td>
					<code style="font-size: 14px; padding: 8px; background: #f0f0f1; display: inline-block;">
						<?php echo esc_html( $api_key ); ?>
					</code>
					<button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js( $api_key ); ?>'); alert('API key copied!');">
						<?php esc_html_e( 'Copy', 'third-audience' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'Use this API key in your headless frontend to authenticate requests.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Webhook URL', 'third-audience' ); ?></label>
				</th>
				<td>
					<code style="font-size: 14px; padding: 8px; background: #f0f0f1; display: inline-block;">
						<?php echo esc_html( $wizard->get_webhook_url() ); ?>
					</code>
					<button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js( $wizard->get_webhook_url() ); ?>'); alert('Webhook URL copied!');">
						<?php esc_html_e( 'Copy', 'third-audience' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'Configure your frontend to send cache invalidation requests to this URL.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>
		</table>
	</div>

	<!-- Code Snippets Section -->
	<?php if ( 'nextjs' === $settings['framework'] ) : ?>
		<div class="ta-settings-section" style="margin-top: 30px;">
			<h2><?php esc_html_e( 'Next.js Integration Code', 'third-audience' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Copy and paste this code into your Next.js project.', 'third-audience' ); ?>
			</p>

			<div style="margin-top: 15px;">
				<h3><?php esc_html_e( 'Environment Variables & Helper Function', 'third-audience' ); ?></h3>
				<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px;"><code><?php echo esc_html( $wizard->get_nextjs_snippet( $settings['frontend_url'] ) ); ?></code></pre>
				<button type="button" class="button" onclick="navigator.clipboard.writeText(<?php echo wp_json_encode( $wizard->get_nextjs_snippet( $settings['frontend_url'] ) ); ?>); alert('Code copied!');">
					<?php esc_html_e( 'Copy Code', 'third-audience' ); ?>
				</button>
			</div>
		</div>
	<?php endif; ?>

	<!-- CORS Configuration Section -->
	<?php if ( in_array( $settings['server_type'], array( 'nginx', 'apache' ), true ) ) : ?>
		<div class="ta-settings-section" style="margin-top: 30px;">
			<h2><?php esc_html_e( 'CORS Configuration', 'third-audience' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Add this configuration to your server to allow cross-origin requests from your frontend.', 'third-audience' ); ?>
			</p>

			<div style="margin-top: 15px;">
				<h3>
					<?php
					echo 'nginx' === $settings['server_type']
						? esc_html__( 'Nginx Configuration', 'third-audience' )
						: esc_html__( 'Apache Configuration (.htaccess)', 'third-audience' );
					?>
				</h3>
				<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px;"><code><?php echo esc_html( $wizard->get_cors_snippet( $settings['server_type'], $settings['frontend_url'] ) ); ?></code></pre>
				<button type="button" class="button" onclick="navigator.clipboard.writeText(<?php echo wp_json_encode( $wizard->get_cors_snippet( $settings['server_type'], $settings['frontend_url'] ) ); ?>); alert('Configuration copied!');">
					<?php esc_html_e( 'Copy Configuration', 'third-audience' ); ?>
				</button>
			</div>
		</div>
	<?php endif; ?>

	<!-- Citation Tracking Section -->
	<div class="ta-settings-section" style="margin-top: 30px;">
		<h2><?php esc_html_e( 'Citation Tracking API', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Track AI citation clicks (utm_source=chatgpt.com, etc.) from your headless frontend.', 'third-audience' ); ?>
		</p>

		<?php
		$citation_api_key = get_option( 'ta_headless_api_key', '' );
		if ( empty( $citation_api_key ) ) {
			$citation_api_key = wp_generate_password( 32, false );
			update_option( 'ta_headless_api_key', $citation_api_key );
		}
		$citation_endpoint = rest_url( 'third-audience/v1/track-citation' );
		?>

		<table class="form-table" role="presentation">
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Citation API Endpoint', 'third-audience' ); ?></label>
				</th>
				<td>
					<code style="font-size: 14px; padding: 8px; background: #f0f0f1; display: inline-block;">
						POST <?php echo esc_html( $citation_endpoint ); ?>
					</code>
					<button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js( $citation_endpoint ); ?>'); alert('Endpoint URL copied!');">
						<?php esc_html_e( 'Copy', 'third-audience' ); ?>
					</button>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label><?php esc_html_e( 'Citation API Key', 'third-audience' ); ?></label>
				</th>
				<td>
					<code style="font-size: 14px; padding: 8px; background: #f0f0f1; display: inline-block;">
						<?php echo esc_html( $citation_api_key ); ?>
					</code>
					<button type="button" class="button" onclick="navigator.clipboard.writeText('<?php echo esc_js( $citation_api_key ); ?>'); alert('API Key copied!');">
						<?php esc_html_e( 'Copy', 'third-audience' ); ?>
					</button>
					<p class="description">
						<?php esc_html_e( 'Send this key in X-TA-Api-Key header.', 'third-audience' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<div style="margin-top: 15px;">
			<h3><?php esc_html_e( 'Next.js Middleware for Citation Tracking', 'third-audience' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Add this to your middleware.ts to track AI citations:', 'third-audience' ); ?></p>
			<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px;"><code>// .env.local
TA_CITATION_API_URL=<?php echo esc_html( $citation_endpoint ); ?>

TA_CITATION_API_KEY=<?php echo esc_html( $citation_api_key ); ?>


// middleware.ts - Add this detection code
const AI_CITATION_SOURCES = [
  { pattern: /chatgpt/i, name: 'ChatGPT' },
  { pattern: /perplexity/i, name: 'Perplexity' },
  { pattern: /claude/i, name: 'Claude' },
  { pattern: /gemini/i, name: 'Gemini' },
];

function detectAICitation(request: NextRequest): { platform: string; query?: string } | null {
  const url = request.nextUrl;
  const referer = request.headers.get('referer') || '';

  // Check UTM parameters
  const utmSource = url.searchParams.get('utm_source');
  if (utmSource) {
    for (const source of AI_CITATION_SOURCES) {
      if (source.pattern.test(utmSource)) {
        return { platform: source.name };
      }
    }
  }

  // Check referer
  for (const source of AI_CITATION_SOURCES) {
    if (source.pattern.test(referer)) {
      // Extract query from Perplexity referer
      if (source.name === 'Perplexity' && referer.includes('?q=')) {
        const match = referer.match(/[?&]q=([^&]+)/);
        return { platform: source.name, query: match ? decodeURIComponent(match[1]) : undefined };
      }
      return { platform: source.name };
    }
  }

  return null;
}

// In your middleware function:
export async function middleware(request: NextRequest) {
  const citation = detectAICitation(request);
  if (citation) {
    // Fire and forget - non-blocking
    fetch(process.env.TA_CITATION_API_URL!, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-TA-Api-Key': process.env.TA_CITATION_API_KEY!,
      },
      body: JSON.stringify({
        url: request.nextUrl.pathname,
        platform: citation.platform,
        referer: request.headers.get('referer') || '',
        search_query: citation.query || '',
        ip: request.headers.get('x-forwarded-for')?.split(',')[0] || 'unknown',
      }),
    }).catch(() => {}); // Ignore errors
  }

  return NextResponse.next();
}</code></pre>
			<button type="button" class="button" onclick="navigator.clipboard.writeText(document.querySelector('pre code').textContent); alert('Code copied!');">
				<?php esc_html_e( 'Copy Code', 'third-audience' ); ?>
			</button>
		</div>
	</div>

	<!-- Help Section -->
	<div class="ta-settings-section" style="margin-top: 30px; background: #f0f6fc; padding: 20px; border-left: 4px solid #0073aa;">
		<h3 style="margin-top: 0;"><?php esc_html_e( 'Next Steps', 'third-audience' ); ?></h3>
		<ol style="line-height: 1.8;">
			<li><?php esc_html_e( 'Copy the API key and add it to your frontend\'s environment variables', 'third-audience' ); ?></li>
			<li><?php esc_html_e( 'Add the citation tracking middleware code to your Next.js project', 'third-audience' ); ?></li>
			<li><?php esc_html_e( 'Configure CORS headers on your WordPress server', 'third-audience' ); ?></li>
			<li><?php esc_html_e( 'Test by visiting your site with ?utm_source=chatgpt.com', 'third-audience' ); ?></li>
		</ol>
	</div>
<?php endif; ?>
