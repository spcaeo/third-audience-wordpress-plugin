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
		<?php wp_nonce_field( 'ta_save_headless_settings', 'ta_nonce' ); ?>
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
	<!-- Citation Tracking Setup Options -->
	<?php
	$citation_api_key_setup = get_option( 'ta_headless_api_key', '' );
	if ( empty( $citation_api_key_setup ) ) {
		$citation_api_key_setup = wp_generate_password( 32, false );
		update_option( 'ta_headless_api_key', $citation_api_key_setup );
	}
	$citation_endpoint_setup = rest_url( 'third-audience/v1/track-citation' );
	?>

	<div class="ta-settings-section" style="margin-top: 30px;">
		<h2><?php esc_html_e( 'Citation Tracking Setup', 'third-audience' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Set up AI citation tracking (ChatGPT, Perplexity, Claude, etc.) using Next.js middleware with AJAX-first architecture.', 'third-audience' ); ?>
		</p>

		<!-- Server-Side Middleware Setup -->
		<div style="margin-top: 20px; padding: 25px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
			<div style="display: flex; gap: 20px; align-items: flex-start; margin-bottom: 20px;">
				<div style="background: #667eea; color: white; padding: 10px; border-radius: 8px;">
					<span class="dashicons dashicons-admin-tools" style="font-size: 24px;"></span>
				</div>
				<div>
					<h3 style="margin: 0 0 8px 0;"><?php esc_html_e( 'Server-Side Middleware (Recommended)', 'third-audience' ); ?></h3>
					<p style="margin: 0; color: #666;">
						<?php esc_html_e( 'Track citations using Next.js middleware with AJAX-first approach. Works with ALL security plugins.', 'third-audience' ); ?>
					</p>
				</div>
			</div>

			<!-- Benefits -->
			<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 20px;">
				<div style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #e0e0e0;">
					<span class="dashicons dashicons-visibility" style="color: #667eea;"></span>
					<strong><?php esc_html_e( 'Server-Side', 'third-audience' ); ?></strong>
					<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><?php esc_html_e( 'Tracks before page renders, more reliable', 'third-audience' ); ?></p>
				</div>
				<div style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #e0e0e0;">
					<span class="dashicons dashicons-lock" style="color: #0073aa;"></span>
					<strong><?php esc_html_e( 'Hidden API Key', 'third-audience' ); ?></strong>
					<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><?php esc_html_e( 'Key stays in .env, not exposed to client', 'third-audience' ); ?></p>
				</div>
				<div style="background: white; padding: 12px; border-radius: 6px; border: 1px solid #e0e0e0;">
					<span class="dashicons dashicons-admin-network" style="color: #764ba2;"></span>
					<strong><?php esc_html_e( 'AJAX-First', 'third-audience' ); ?></strong>
					<p style="margin: 5px 0 0 0; font-size: 12px; color: #666;"><?php esc_html_e( 'Works with ALL security plugins, no REST API blocks', 'third-audience' ); ?></p>
				</div>
			</div>

			<button type="button" class="button button-primary" id="ta-open-setup-guide" style="padding: 10px 25px;">
				<span class="dashicons dashicons-welcome-learn-more" style="margin-right: 5px;"></span>
				<?php esc_html_e( 'Open Full Setup Guide', 'third-audience' ); ?>
			</button>
		</div>
	</div>

	<!-- Developer Setup Guide Modal -->
	<div id="ta-setup-guide-modal" class="ta-modal" style="display: none;">
		<div class="ta-modal-overlay"></div>
		<div class="ta-modal-content" style="max-width: 800px; max-height: 90vh; overflow-y: auto;">
			<div class="ta-modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px 8px 0 0;">
				<h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
					<span class="dashicons dashicons-book-alt"></span>
					<?php esc_html_e( 'Citation Tracking Setup Guide', 'third-audience' ); ?>
				</h2>
				<p style="margin: 10px 0 0 0; opacity: 0.9;">
					<?php esc_html_e( 'Follow these steps to enable AI citation tracking on your headless frontend.', 'third-audience' ); ?>
				</p>
				<button type="button" class="ta-modal-close" style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.2); border: none; color: white; width: 30px; height: 30px; border-radius: 50%; cursor: pointer; font-size: 18px;">&times;</button>
			</div>
			<div class="ta-modal-body" style="padding: 25px;">
				<?php
				$citation_api_key_modal = get_option( 'ta_headless_api_key', '' );
				if ( empty( $citation_api_key_modal ) ) {
					$citation_api_key_modal = wp_generate_password( 32, false );
					update_option( 'ta_headless_api_key', $citation_api_key_modal );
				}
				$citation_endpoint_modal = rest_url( 'third-audience/v1/track-citation' );
				?>

				<!-- Step 1 -->
				<div class="ta-setup-step" style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
					<div style="display: flex; align-items: flex-start; gap: 15px;">
						<div style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">1</div>
						<div style="flex: 1;">
							<h3 style="margin: 0 0 10px 0; color: #1e1e1e;"><?php esc_html_e( 'Add Environment Variables', 'third-audience' ); ?></h3>
							<p style="margin: 0 0 15px 0; color: #666;">
								<?php esc_html_e( 'Add these to your .env.local file in your Next.js project root:', 'third-audience' ); ?>
							</p>
							<div style="background: #1e1e1e; border-radius: 6px; padding: 15px; position: relative;">
								<pre style="margin: 0; color: #d4d4d4; font-size: 13px; overflow-x: auto;"><code id="env-vars-code">WORDPRESS_URL=<?php echo esc_html( rtrim( home_url(), '/' ) ); ?>

TA_CITATION_API_KEY=<?php echo esc_html( $citation_api_key_modal ); ?></code></pre>
								<button type="button" class="button ta-copy-btn" data-target="env-vars-code" style="position: absolute; top: 10px; right: 10px; padding: 5px 12px; font-size: 12px;">
									<span class="dashicons dashicons-clipboard" style="font-size: 14px; margin-right: 3px;"></span><?php esc_html_e( 'Copy', 'third-audience' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Step 2 -->
				<div class="ta-setup-step" style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
					<div style="display: flex; align-items: flex-start; gap: 15px;">
						<div style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">2</div>
						<div style="flex: 1;">
							<h3 style="margin: 0 0 10px 0; color: #1e1e1e;"><?php esc_html_e( 'Create or Update middleware.ts', 'third-audience' ); ?></h3>
							<p style="margin: 0 0 15px 0; color: #666;">
								<?php esc_html_e( 'Create src/middleware.ts (or update existing) with this code:', 'third-audience' ); ?>
							</p>
							<div style="background: #1e1e1e; border-radius: 6px; padding: 15px; position: relative; max-height: 400px; overflow-y: auto;">
								<pre style="margin: 0; color: #d4d4d4; font-size: 12px; overflow-x: auto;"><code id="middleware-code">import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

// AI platforms that cite content
const AI_CITATION_SOURCES = [
  { pattern: /chatgpt/i, name: 'ChatGPT' },
  { pattern: /perplexity/i, name: 'Perplexity' },
  { pattern: /claude/i, name: 'Claude' },
  { pattern: /gemini/i, name: 'Gemini' },
  { pattern: /copilot/i, name: 'Copilot' },
  { pattern: /bing/i, name: 'Bing AI' },
];

/**
 * Detect if request came from an AI citation
 */
function detectAICitation(request: NextRequest): { platform: string; query?: string } | null {
  const url = request.nextUrl;
  const referer = request.headers.get('referer') || '';

  // Check utm_source parameter (e.g., ?utm_source=chatgpt.com)
  const utmSource = url.searchParams.get('utm_source');
  if (utmSource) {
    for (const source of AI_CITATION_SOURCES) {
      if (source.pattern.test(utmSource)) {
        return { platform: source.name };
      }
    }
  }

  // Check referer header
  for (const source of AI_CITATION_SOURCES) {
    if (source.pattern.test(referer)) {
      // Extract search query from Perplexity
      if (source.name === 'Perplexity' && referer.includes('?q=')) {
        const match = referer.match(/[?&]q=([^&]+)/);
        return {
          platform: source.name,
          query: match ? decodeURIComponent(match[1]) : undefined
        };
      }
      return { platform: source.name };
    }
  }

  return null;
}

/**
 * Track citation using AJAX-FIRST architecture
 *
 * Why AJAX first?
 * - Works with ALL security plugins (Solid Security, Wordfence, etc.)
 * - Standard WordPress API method since WP 2.8
 * - No REST API conflicts or blocks
 */
async function trackCitation(request: NextRequest, citation: { platform: string; query?: string }) {
  const wordpressUrl = process.env.WORDPRESS_URL || 'https://your-site.com';
  const apiKey = process.env.TA_CITATION_API_KEY || '';

  const data = {
    url: request.nextUrl.pathname,
    platform: citation.platform,
    referer: request.headers.get('referer') || '',
    search_query: citation.query || '',
    ip: request.headers.get('x-forwarded-for')?.split(',')[0] || 'unknown',
    client_user_agent: request.headers.get('user-agent') || '',
  };

  try {
    // METHOD 1: AJAX (primary - works with ALL security plugins)
    const ajaxResponse = await fetch(`${wordpressUrl}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'ta_track_citation',
        api_key: apiKey,
        ...data,
      }),
    });

    if (ajaxResponse.ok) {
      console.log('[Citation] ✅ Tracked via AJAX');
      return;
    }

    // METHOD 2: REST API (fallback - may be blocked by security plugins)
    const restResponse = await fetch(`${wordpressUrl}/wp-json/third-audience/v1/track-citation`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-TA-Api-Key': apiKey,
      },
      body: JSON.stringify(data),
    });

    if (restResponse.ok) {
      console.log('[Citation] ✅ Tracked via REST API');
      return;
    }

    console.error('[Citation] ❌ All methods failed');
  } catch (error) {
    console.error('[Citation] ❌ Error:', error);
  }
}

export async function middleware(request: NextRequest) {
  // Detect AI citation
  const citation = detectAICitation(request);

  if (citation) {
    // Track asynchronously (non-blocking)
    trackCitation(request, citation);
  }

  return NextResponse.next();
}

// Configure which paths trigger middleware
export const config = {
  matcher: [
    // Match all paths except static files and API routes
    '/((?!api|_next/static|_next/image|favicon.ico).*)',
  ],
};</code></pre>
								<button type="button" class="button ta-copy-btn" data-target="middleware-code" style="position: absolute; top: 10px; right: 10px; padding: 5px 12px; font-size: 12px;">
									<span class="dashicons dashicons-clipboard" style="font-size: 14px; margin-right: 3px;"></span><?php esc_html_e( 'Copy', 'third-audience' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Step 3 -->
				<div class="ta-setup-step" style="margin-bottom: 25px; padding: 20px; background: #f8f9fa; border-radius: 8px; border-left: 4px solid #667eea;">
					<div style="display: flex; align-items: flex-start; gap: 15px;">
						<div style="background: #667eea; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0;">3</div>
						<div style="flex: 1;">
							<h3 style="margin: 0 0 10px 0; color: #1e1e1e;"><?php esc_html_e( 'Deploy & Test', 'third-audience' ); ?></h3>
							<p style="margin: 0 0 15px 0; color: #666;">
								<?php esc_html_e( 'After deploying, test citation tracking:', 'third-audience' ); ?>
							</p>
							<ol style="margin: 0; padding-left: 20px; color: #444; line-height: 1.8;">
								<li><?php esc_html_e( 'Restart your Next.js development server (or redeploy)', 'third-audience' ); ?></li>
								<li>
									<?php esc_html_e( 'Visit your site with a test parameter:', 'third-audience' ); ?>
									<code style="background: #e9ecef; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
										<?php echo esc_html( $settings['frontend_url'] ); ?>?utm_source=chatgpt.com
									</code>
								</li>
								<li><?php esc_html_e( 'Check WordPress Admin → Bot Analytics → AI Citations', 'third-audience' ); ?></li>
								<li><?php esc_html_e( 'You should see the citation logged within a few seconds', 'third-audience' ); ?></li>
							</ol>
						</div>
					</div>
				</div>

				<!-- Quick Reference Card -->
				<div style="background: #e8f4fd; border: 1px solid #b8daff; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
					<h3 style="margin: 0 0 15px 0; color: #004085; display: flex; align-items: center; gap: 8px;">
						<span class="dashicons dashicons-info"></span>
						<?php esc_html_e( 'Quick Reference', 'third-audience' ); ?>
					</h3>
					<table style="width: 100%; border-collapse: collapse; font-size: 13px;">
						<tr>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff; font-weight: 600; width: 140px;"><?php esc_html_e( 'WordPress URL', 'third-audience' ); ?></td>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff;">
								<code style="background: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?php echo esc_html( rtrim( home_url(), '/' ) ); ?></code>
							</td>
						</tr>
						<tr>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff; font-weight: 600;"><?php esc_html_e( 'AJAX Endpoint', 'third-audience' ); ?></td>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff;">
								<code style="background: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">/wp-admin/admin-ajax.php</code>
								<span style="background: #46b450; color: white; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px;">Primary</span>
							</td>
						</tr>
						<tr>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff; font-weight: 600;"><?php esc_html_e( 'REST Endpoint', 'third-audience' ); ?></td>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff;">
								<code style="background: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?php echo esc_html( $citation_endpoint_modal ); ?></code>
								<span style="background: #f0f0f1; color: #666; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 8px;">Fallback</span>
							</td>
						</tr>
						<tr>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff; font-weight: 600;"><?php esc_html_e( 'API Key', 'third-audience' ); ?></td>
							<td style="padding: 8px 0; border-bottom: 1px solid #b8daff;">
								<code style="background: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;"><?php echo esc_html( $citation_api_key_modal ); ?></code>
							</td>
						</tr>
						<tr>
							<td style="padding: 8px 0; font-weight: 600;"><?php esc_html_e( 'HTTP Method', 'third-audience' ); ?></td>
							<td style="padding: 8px 0;">
								<code style="background: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">POST</code>
							</td>
						</tr>
					</table>
				</div>

				<!-- Troubleshooting -->
				<div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px;">
					<h3 style="margin: 0 0 15px 0; color: #856404; display: flex; align-items: center; gap: 8px;">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Troubleshooting', 'third-audience' ); ?>
					</h3>
					<ul style="margin: 0; padding-left: 20px; color: #856404; line-height: 1.8; font-size: 13px;">
						<li><strong><?php esc_html_e( 'Citations not appearing?', 'third-audience' ); ?></strong> <?php esc_html_e( 'Check browser console for errors. Verify env vars are set correctly.', 'third-audience' ); ?></li>
						<li><strong><?php esc_html_e( 'CORS errors?', 'third-audience' ); ?></strong> <?php esc_html_e( 'Ensure your WordPress server allows requests from your frontend domain.', 'third-audience' ); ?></li>
						<li><strong><?php esc_html_e( '401 Unauthorized?', 'third-audience' ); ?></strong> <?php esc_html_e( 'API key mismatch. Copy the key again from this page.', 'third-audience' ); ?></li>
						<li><strong><?php esc_html_e( 'Middleware not running?', 'third-audience' ); ?></strong> <?php esc_html_e( 'Check the matcher config. Ensure middleware.ts is in src/ or root.', 'third-audience' ); ?></li>
					</ul>
				</div>
			</div>
			<div class="ta-modal-footer" style="padding: 15px 25px; border-top: 1px solid #ddd; text-align: right; background: #f8f9fa; border-radius: 0 0 8px 8px;">
				<button type="button" class="button button-primary ta-modal-close-btn">
					<?php esc_html_e( 'Got it!', 'third-audience' ); ?>
				</button>
			</div>
		</div>
	</div>

	<!-- Modal Styles and Scripts -->
	<style>
		.ta-modal {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 100000;
			display: flex;
			align-items: center;
			justify-content: center;
		}
		.ta-modal-overlay {
			position: absolute;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0, 0, 0, 0.6);
		}
		.ta-modal-content {
			position: relative;
			background: white;
			border-radius: 8px;
			box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
			width: 90%;
			animation: ta-modal-appear 0.2s ease-out;
		}
		@keyframes ta-modal-appear {
			from {
				opacity: 0;
				transform: scale(0.95) translateY(-20px);
			}
			to {
				opacity: 1;
				transform: scale(1) translateY(0);
			}
		}
		.ta-copy-btn.copied {
			background: #46b450 !important;
			color: white !important;
			border-color: #46b450 !important;
		}
	</style>
	<script>
	jQuery(document).ready(function($) {
		// Open modal
		$('#ta-open-setup-guide').on('click', function() {
			$('#ta-setup-guide-modal').fadeIn(200);
			$('body').css('overflow', 'hidden');
		});

		// Close modal
		$('.ta-modal-close, .ta-modal-close-btn, .ta-modal-overlay').on('click', function() {
			$('#ta-setup-guide-modal').fadeOut(200);
			$('body').css('overflow', '');
		});

		// Close on Escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $('#ta-setup-guide-modal').is(':visible')) {
				$('#ta-setup-guide-modal').fadeOut(200);
				$('body').css('overflow', '');
			}
		});

		// Copy buttons
		$('.ta-copy-btn').on('click', function() {
			var targetId = $(this).data('target');
			var text = $('#' + targetId).text();
			var btn = $(this);

			navigator.clipboard.writeText(text).then(function() {
				btn.addClass('copied').html('<span class="dashicons dashicons-yes" style="font-size: 14px; margin-right: 3px;"></span><?php echo esc_js( __( 'Copied!', 'third-audience' ) ); ?>');
				setTimeout(function() {
					btn.removeClass('copied').html('<span class="dashicons dashicons-clipboard" style="font-size: 14px; margin-right: 3px;"></span><?php echo esc_js( __( 'Copy', 'third-audience' ) ); ?>');
				}, 2000);
			});
		});
	});
	</script>

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
			<h3><?php esc_html_e( 'Next.js Middleware for Citation Tracking (AJAX-First)', 'third-audience' ); ?></h3>
			<p class="description"><?php esc_html_e( 'Add this to your middleware.ts to track AI citations. Uses AJAX first (works with ALL security plugins):', 'third-audience' ); ?></p>
			<pre style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 4px; overflow-x: auto; font-size: 13px;"><code>// .env.local
WORDPRESS_URL=<?php echo esc_html( rtrim( home_url(), '/' ) ); ?>

TA_CITATION_API_KEY=<?php echo esc_html( $citation_api_key ); ?>


// middleware.ts - AJAX-first approach
const AI_CITATION_SOURCES = [
  { pattern: /chatgpt/i, name: 'ChatGPT' },
  { pattern: /perplexity/i, name: 'Perplexity' },
  { pattern: /claude/i, name: 'Claude' },
  { pattern: /gemini/i, name: 'Gemini' },
  { pattern: /copilot/i, name: 'Copilot' },
  { pattern: /bing/i, name: 'Bing AI' },
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
      if (source.name === 'Perplexity' && referer.includes('?q=')) {
        const match = referer.match(/[?&]q=([^&]+)/);
        return { platform: source.name, query: match ? decodeURIComponent(match[1]) : undefined };
      }
      return { platform: source.name };
    }
  }

  return null;
}

async function trackCitation(request: NextRequest, citation: { platform: string; query?: string }) {
  const wordpressUrl = process.env.WORDPRESS_URL;
  const apiKey = process.env.TA_CITATION_API_KEY;

  const data = {
    url: request.nextUrl.pathname,
    platform: citation.platform,
    referer: request.headers.get('referer') || '',
    search_query: citation.query || '',
    ip: request.headers.get('x-forwarded-for')?.split(',')[0] || 'unknown',
    client_user_agent: request.headers.get('user-agent') || '',
  };

  try {
    // AJAX first (works with ALL security plugins)
    const ajaxResponse = await fetch(`${wordpressUrl}/wp-admin/admin-ajax.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'ta_track_citation',
        api_key: apiKey,
        ...data,
      }),
    });

    if (ajaxResponse.ok) return;

    // Fallback to REST API if AJAX fails
    await fetch(`${wordpressUrl}/wp-json/third-audience/v1/track-citation`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-TA-Api-Key': apiKey,
      },
      body: JSON.stringify(data),
    });
  } catch (error) {
    console.error('[Citation] Error:', error);
  }
}

export async function middleware(request: NextRequest) {
  const citation = detectAICitation(request);
  if (citation) {
    trackCitation(request, citation); // Fire and forget
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
