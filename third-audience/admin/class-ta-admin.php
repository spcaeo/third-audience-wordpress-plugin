
	/**
	 * AJAX handler: Export cache entries to CSV.
	 *
	 * Supports exporting selected entries, filtered view, or all entries.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function ajax_export_cache_entries() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$export_type = isset( $_POST['export_type'] ) ? sanitize_text_field( wp_unslash( $_POST['export_type'] ) ) : 'filtered';
		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();

		// Validate export type.
		if ( ! in_array( $export_type, array( 'selected', 'filtered', 'all' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid export type.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$csv_data = array();

		// Add CSV header.
		$csv_data[] = array(
			'Third Audience Cache Export',
		);
		$csv_data[] = array(
			'Generated',
			gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		);
		$csv_data[] = array(
			'Export Type',
			ucfirst( $export_type ),
		);
		$csv_data[] = array(); // Empty row for spacing.

		// CSV column headers.
		$csv_data[] = array(
			'URL',
			'Title',
			'Size',
			'Size (bytes)',
			'Expires In',
			'Cache Key',
		);

		// Collect cache entries based on export type.
		$cache_entries = array();

		if ( 'selected' === $export_type ) {
			// Export selected entries only.
			if ( empty( $cache_keys ) ) {
				wp_send_json_error( array( 'message' => __( 'No entries selected for export.', 'third-audience' ) ) );
			}

			foreach ( $cache_keys as $cache_key ) {
				$entry = $cache_manager->get_entry_details( $cache_key );
				if ( $entry ) {
					$cache_entries[] = $entry;
				}
			}
		} elseif ( 'filtered' === $export_type ) {
			// Export filtered/sorted view.
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$filters = isset( $_POST['filters'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['filters'] ) ) : array();
			$orderby = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'expiration';
			$order = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';

			// Get all matching entries (no pagination limit).
			$cache_entries = $cache_manager->get_cache_entries( 10000, 0, $search, $filters, $orderby, $order );
		} else {
			// Export all entries.
			$cache_entries = $cache_manager->get_cache_entries( 10000, 0, '', array(), 'expiration', 'DESC' );
		}

		// Add entry data to CSV.
		foreach ( $cache_entries as $entry ) {
			$csv_data[] = array(
				$entry['url'] ?? '',
				$entry['title'] ?? '',
				$entry['size_human'] ?? '',
				$entry['size'] ?? 0,
				$entry['expires_in'] ?? '',
				$entry['cache_key'] ?? '',
			);
		}

		// Generate CSV output.
		$filename = 'cache-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		wp_send_json_success( array(
			'filename' => $filename,
			'csv_data' => $csv_data,
			'count'    => count( $cache_entries ),
		) );
	}

	/**
	 * AJAX handler: Export cache entries to CSV.
	 *
	 * Supports exporting selected entries, filtered view, or all entries.
	 *
	 * @since 2.0.7
	 * @return void
	 */
	public function ajax_export_cache_entries() {
		$this->security->verify_ajax_request( 'cache_browser' );

		$export_type = isset( $_POST['export_type'] ) ? sanitize_text_field( wp_unslash( $_POST['export_type'] ) ) : 'filtered';
		$cache_keys = isset( $_POST['cache_keys'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['cache_keys'] ) ) : array();

		// Validate export type.
		if ( ! in_array( $export_type, array( 'selected', 'filtered', 'all' ), true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid export type.', 'third-audience' ) ) );
		}

		$cache_manager = new TA_Cache_Manager();
		$csv_data = array();

		// Add CSV header.
		$csv_data[] = array(
			'Third Audience Cache Export',
		);
		$csv_data[] = array(
			'Generated',
			gmdate( 'Y-m-d H:i:s' ) . ' UTC',
		);
		$csv_data[] = array(
			'Export Type',
			ucfirst( $export_type ),
		);
		$csv_data[] = array(); // Empty row for spacing.

		// CSV column headers.
		$csv_data[] = array(
			'URL',
			'Title',
			'Size',
			'Size (bytes)',
			'Expires In',
			'Cache Key',
		);

		// Collect cache entries based on export type.
		$cache_entries = array();

		if ( 'selected' === $export_type ) {
			// Export selected entries only.
			if ( empty( $cache_keys ) ) {
				wp_send_json_error( array( 'message' => __( 'No entries selected for export.', 'third-audience' ) ) );
			}

			foreach ( $cache_keys as $cache_key ) {
				$entry = $cache_manager->get_entry_details( $cache_key );
				if ( $entry ) {
					$cache_entries[] = $entry;
				}
			}
		} elseif ( 'filtered' === $export_type ) {
			// Export filtered/sorted view.
			$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
			$filters = isset( $_POST['filters'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['filters'] ) ) : array();
			$orderby = isset( $_POST['orderby'] ) ? sanitize_text_field( wp_unslash( $_POST['orderby'] ) ) : 'expiration';
			$order = isset( $_POST['order'] ) ? sanitize_text_field( wp_unslash( $_POST['order'] ) ) : 'DESC';

			// Get all matching entries (no pagination limit).
			$cache_entries = $cache_manager->get_cache_entries( 10000, 0, $search, $filters, $orderby, $order );
		} else {
			// Export all entries.
			$cache_entries = $cache_manager->get_cache_entries( 10000, 0, '', array(), 'expiration', 'DESC' );
		}

		// Add entry data to CSV.
		foreach ( $cache_entries as $entry ) {
			$csv_data[] = array(
				$entry['url'] ?? '',
				$entry['title'] ?? '',
				$entry['size_human'] ?? '',
				$entry['size'] ?? 0,
				$entry['expires_in'] ?? '',
				$entry['cache_key'] ?? '',
			);
		}

		// Generate CSV output.
		$filename = 'cache-export-' . gmdate( 'Y-m-d-H-i-s' ) . '.csv';

		wp_send_json_success( array(
			'filename' => $filename,
			'csv_data' => $csv_data,
			'count'    => count( $cache_entries ),
		) );
	}
}
