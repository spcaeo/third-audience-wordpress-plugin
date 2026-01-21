---
date: 2026-01-16T10:56:06-05:00
session_name: general
researcher: Claude
git_commit: none
branch: main
repository: third-audience-jeel
topic: "Pre-generate Markdown on Post Save - v1.3.0 Implementation"
tags: [implementation, wordpress, markdown, pre-generation, third-audience]
status: needs_reapplication
last_updated: 2026-01-16
last_updated_by: Claude
type: implementation_strategy
root_span_id: ""
turn_span_id: ""
---

# Handoff: Pre-generate Markdown Implementation (v1.3.0)

## Task(s)

| Task | Status |
|------|--------|
| Add pre_generate_markdown() method to cache manager | NEEDS REAPPLICATION |
| Register save_post hook for pre-generation | NEEDS REAPPLICATION |
| Modify URL router to check post_meta first | NEEDS REAPPLICATION |
| Add admin settings toggle | NEEDS REAPPLICATION |
| Update plugin version to 1.3.0 | NEEDS REAPPLICATION |
| Test implementation locally | NOT STARTED |

**CRITICAL**: Files were lost during Docker operations. All edits need to be re-applied to the restored v1.2.0 codebase.

## Critical References

1. **Original Handoff**: `thoughts/shared/handoffs/general/2026-01-16_10-13-59_third-audience-complete-implementation.md`
2. **Architecture Doc**: `docs/ARCHITECTURE-DOCUMENTATION.md`
3. **Dries Buytaert's Article**: https://dri.es/the-third-audience (rationale for pre-generation)

## Recent changes

**ALL CHANGES WERE LOST** - Files restored from `third-audience-v1.2.0.zip`. The following changes need to be re-applied:

## Implementation Details (MUST RE-APPLY)

### 1. class-ta-cache-manager.php - Add Pre-generation Methods

Add AFTER the `invalidate_post_cache` method (around line 291):

```php
// =========================================================================
// Pre-generation (Save on Publish)
// =========================================================================

const META_MARKDOWN = '_ta_markdown';
const META_GENERATED = '_ta_markdown_generated';

public function pre_generate_markdown( $post_id, $post ) {
    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return false;
    }
    if ( 'publish' !== $post->post_status ) {
        delete_post_meta( $post_id, self::META_MARKDOWN );
        delete_post_meta( $post_id, self::META_GENERATED );
        return false;
    }
    $enabled_types = get_option( 'ta_enabled_post_types', array( 'post', 'page' ) );
    if ( ! in_array( $post->post_type, $enabled_types, true ) ) {
        return false;
    }
    $url = get_permalink( $post_id );
    if ( ! $url ) return false;

    $api_client = new TA_API_Client();
    $markdown = $api_client->convert_url( $url );

    if ( is_wp_error( $markdown ) ) {
        do_action( 'ta_pre_generation_failed', $post_id, $url, $markdown );
        return false;
    }

    update_post_meta( $post_id, self::META_MARKDOWN, $markdown );
    update_post_meta( $post_id, self::META_GENERATED, time() );

    $cache_key = $this->generate_key( $url );
    $this->set( $cache_key, $markdown );

    do_action( 'ta_markdown_pre_generated', $post_id, $url, $markdown );
    return true;
}

public function get_pre_generated_markdown( $post_id ) {
    $markdown = get_post_meta( $post_id, self::META_MARKDOWN, true );
    return empty( $markdown ) ? false : $markdown;
}

public function get_pre_generated_timestamp( $post_id ) {
    $timestamp = get_post_meta( $post_id, self::META_GENERATED, true );
    return empty( $timestamp ) ? false : (int) $timestamp;
}

public function has_fresh_pre_generated( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) return false;
    $generated_time = $this->get_pre_generated_timestamp( $post_id );
    if ( ! $generated_time ) return false;
    $modified_time = strtotime( $post->post_modified_gmt );
    return $generated_time >= $modified_time;
}

public function regenerate_markdown( $post_id ) {
    $post = get_post( $post_id );
    return $post ? $this->pre_generate_markdown( $post_id, $post ) : false;
}
```

### 2. class-third-audience.php - Register Hook

Add after cache invalidation hooks (around line 86):

```php
// Pre-generate markdown on post save (runs after cache invalidation)
if (get_option('ta_enable_pre_generation', true)) {
    add_action('save_post', array($this->cache_manager, 'pre_generate_markdown'), 20, 2);
}
```

### 3. class-ta-url-router.php - Check post_meta First

Replace the cache check section (lines ~147-157) with:

```php
// Priority 1: Check pre-generated markdown in post_meta (fastest)
$pre_generated = $this->cache_manager->get_pre_generated_markdown( $post_id );
if ( false !== $pre_generated && $this->cache_manager->has_fresh_pre_generated( $post_id ) ) {
    $this->send_markdown_response( $pre_generated, true, 'PRE_GENERATED' );
    return;
}

// Priority 2: Check transient cache (fallback)
$cache_key = $this->cache_manager->get_cache_key( $original_url );
$cached = $this->cache_manager->get( $cache_key );
if ( false !== $cached ) {
    $this->send_markdown_response( $cached, true, 'HIT' );
    return;
}
```

Also update `send_markdown_response()` signature:
```php
private function send_markdown_response( $markdown, $cache_hit, $cache_status = null ) {
    if ( null === $cache_status ) {
        $cache_status = $cache_hit ? 'HIT' : 'MISS';
    }
    // ... rest unchanged, use $cache_status in X-Cache-Status header
}
```

### 4. admin/views/settings-page.php - Add Toggle

Add after Discovery Tags row (around line 160):

```php
<tr>
    <th scope="row"><?php esc_html_e( 'Pre-generate Markdown', 'third-audience' ); ?></th>
    <td>
        <label class="ta-checkbox-label">
            <input type="checkbox" name="ta_enable_pre_generation" value="1"
                   <?php checked( get_option( 'ta_enable_pre_generation', true ) ); ?> />
            <?php esc_html_e( 'Generate markdown when posts are published', 'third-audience' ); ?>
        </label>
        <p class="description"><?php esc_html_e( 'Recommended: Pre-generates markdown on save so it\'s always instantly available for AI crawlers.', 'third-audience' ); ?></p>
    </td>
</tr>
```

### 5. admin/class-ta-admin.php - Register Setting

Add after `ta_enable_discovery_tags` registration (around line 189):

```php
register_setting( 'ta_settings', 'ta_enable_pre_generation', array(
    'type'              => 'boolean',
    'sanitize_callback' => 'rest_sanitize_boolean',
    'default'           => true,
) );
```

### 6. third-audience.php - Version Bump

- Line 6: `Version: 1.2.0` → `Version: 1.3.0`
- Line 30: `define( 'TA_VERSION', '1.2.0' )` → `define( 'TA_VERSION', '1.3.0' )`
- Line 37: `define( 'TA_DB_VERSION', '1.2.0' )` → `define( 'TA_DB_VERSION', '1.3.0' )`

Add to defaults array (around line 203):
```php
'ta_enable_pre_generation' => true,
```

Add upgrade migration (after 1.2.x upgrade block, around line 325):
```php
// Upgrade from 1.2.x to 1.3.x.
if ( version_compare( $installed_version, '1.3.0', '<' ) ) {
    if ( false === get_option( 'ta_enable_pre_generation' ) ) {
        update_option( 'ta_enable_pre_generation', true, false );
    }
    $logger->info( 'Pre-generation feature enabled (v1.3.0 upgrade).' );
}
```

## Learnings

1. **Docker volume mounts can conflict**: The plugin directory was mounted as both a bind mount AND part of a named volume, causing sync issues
2. **Always backup before Docker operations**: The files were lost when trying to copy to/from Docker container
3. **Pre-generation rationale**: Per Dries Buytaert's "Third Audience" article, markdown should always be available instantly - not generated on-demand

## Post-Mortem

### What Worked
- Implementation design was solid and complete
- All 6 files were identified and edited correctly
- The pre-generation approach follows best practices from the original Third Audience concept

### What Failed
- **Files lost during Docker operations**: When attempting to copy the updated plugin to the Docker container, the local files were somehow deleted
- **Docker cp command issues**: The `docker cp` command created nested directories incorrectly
- **No git backup**: The project wasn't in a git repository, so there was no way to recover the changes

### Key Decisions
- **Use post_meta over transients**: Permanent storage ensures markdown survives cache clears
- **Priority-based serving**: post_meta → transient → worker (fallback chain)
- **X-Cache-Status header values**: PRE_GENERATED, HIT, MISS for debugging

## Artifacts

- `thoughts/shared/handoffs/general/2026-01-16_10-13-59_third-audience-complete-implementation.md` - Original handoff with context
- `third-audience-v1.2.0.zip` - Backup of working v1.2.0 (restored from this)
- `docs/ARCHITECTURE-DOCUMENTATION.md` - System architecture

## Action Items & Next Steps

1. **Re-apply all 6 edits** listed in "Implementation Details" section above
2. **Test locally**:
   - Start Docker: `docker compose up -d`
   - Create/edit a post in WordPress admin (localhost:8080)
   - Request `.md` URL and verify `X-Cache-Status: PRE_GENERATED` header
3. **Verify version**: Check health endpoint shows v1.3.0
4. **Consider git init**: Initialize git repo to prevent future data loss

## Other Notes

- Docker environment: `docker compose up -d` (WordPress at localhost:8080, phpMyAdmin at localhost:8081)
- Plugin is mounted as volume from `./third-audience` - changes are live
- Worker URL: `https://ta-worker.rp-2ae.workers.dev`
- The cloudflare tunnel URL in WordPress settings may need updating for external testing
