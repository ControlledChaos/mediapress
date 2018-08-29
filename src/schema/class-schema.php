<?php
/**
 * Database Schema helper
 *
 * @package     MediaPress
 * @subpackage  Schema
 * @copyright   Copyright (c) 2018, Brajesh Singh
 * @license     https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author      Brajesh Singh
 * @since       1.0.0
 * @contributor Ravi sharma
 */

namespace PressThemes\MediaPress\Schema;

// Exit if file accessed directly over web.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MediaPress Schema Manager.
 */
class Schema {

	/**
	 * Get table name.
	 *
	 * @param string $name table identifier.
	 *
	 * @return null|string full table name or null.
	 */
	public static function table( $name ) {
		$tables = array(
			'logs'    => 'mpp_logs',
			'media'   => 'mpp_media_items',
			'gallery' => 'mpp_gallery_items',
		);

		global $wpdb;

		return isset( $tables[ $name ] ) ? $wpdb->prefix . $tables[ $name ] : null;
	}

	/**
	 * Create Tables.
	 */
	public static function create() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$log_table     = self::table( 'logs' );
		$media_table   = self::table( 'media' );
		$gallery_table = self::table( 'gallery' );

		$sql = array();

		$sql[] = "CREATE TABLE IF NOT EXISTS {$log_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			item_id bigint(20) NOT NULL,
			action varchar(16) NOT NULL,
			value varchar(32) NOT NULL,
			logged_at timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY (id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE IF NOT EXISTS {$media_table} (
			media_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			gallery_id bigint(20) NOT NULL,
			type varchar(32) NOT NULL,
			status varchar(32) NOT NULL,
			component varchar(32) NOT NULL,
			component_id bigint(20) NOT NULL,
			context varchar(32) NULL,
			storage varchar(50) NOT NULL,
			is_orphan tinyint(1) NOT NULL,
			is_remote tinyint(1) NOT NULL,
			is_raw tinyint(1) NOT NULL,
			is_oembed tinyint(1) NOT NULL,
			source varchar(2083) NOT NULL,
			oembed_content longtext NOT NULL,
			oembed_time DATETIME NOT NULL,
			PRIMARY KEY (media_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE IF NOT EXISTS {$gallery_table} (
			gallery_id bigint(20) NOT NULL,
			user_id bigint(20) NOT NULL,
			type varchar(32) NOT NULL,
			status varchar(32) NOT NULL,
			component varchar(32) NOT NULL,
			component_id bigint(20) NOT NULL,
			PRIMARY KEY (media_id)
		) {$charset_collate};";

		dbDelta( $sql );
	}
}
