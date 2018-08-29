<?php
/**
 * MediaPress Bootstrapper.
 *
 * @package    MediaPress
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace PressThemes\MediaPress\Bootstrap;

// No direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

use MediaPress\Admin\Admin_Helper;
use MediaPress\Handlers\Ajax_Request_Handler;
use PressThemes\MediaPress\Modules\BuddyPress\Members\Members_Loader;
use PressThemes\MediaPress\Modules\BuddyPress\Groups\Groups_Loader;

/**
 * Bootstrapper.
 */
class Bootstrapper {

	/**
	 * Setup the bootstrapper.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Bind hooks
	 */
	private function setup() {

		// Load the MediaPress core.
		add_action( 'plugins_loaded', array( $this, 'load' ), 0 );

		add_action( 'update_option_mpp-settings', array( $this, 'flush_rewrite_rules_on_settings_update' ), 10, 2 );

		add_action( 'init', array( $this, 'load_translations' ) );
	}

	/**
	 * Loads the MediaPress Core Loader class
	 *
	 * Loading is handled by the MPP_Core_Loader
	 */
	public function load() {

		require_once mediapress()->get_path() . 'mpp-loader.php';

		$loader = new MPP_Core_Loader();
		$loader->load();

		do_action( 'mpp_loaded' );
	}

	/**
	 * Flush rewrite rules automatically when our settings is updated and the slug for permalink/archive change.
	 *
	 * @param array $old old settings.
	 * @param array $new new settings.
	 */
	public function flush_rewrite_rules_on_settings_update( $old, $new ) {

		// for the time when there was no old option saved.
		if ( empty( $old ) || empty( $new ) ) {
			flush_rewrite_rules();
			return;
		}

		$old_permalink = isset( $old['gallery_permalink_slug'] )? $old['gallery_permalink_slug'] : false;
		$new_permalink = isset( $new['gallery_permalink_slug'] )? $new['gallery_permalink_slug'] : false;

		$old_archive_slug = isset( $old['gallery_archive_slug'] )? $old['gallery_archive_slug'] : false;
		$new_archive_slug = isset( $new['gallery_archive_slug'] )? $new['gallery_archive_slug'] : false;

		// Detect change in gallery archive/single slug.
		if ( ( $old_archive_slug != $new_archive_slug ) || ( $old_permalink != $new_permalink ) ) {
			// change happened.
			MPP_Post_Type_Helper::get_instance()->init();

			flush_rewrite_rules();
		}
	}

	/**
	 * Load admin
	 */
	public function admin_load() {

		if ( ! is_admin() || defined( 'DOING_AJAX' ) || ! function_exists( 'buddypress' ) ) {
			return;
		}

		// Load pt-settings
		require_once bugmapp()->get_path() . 'src/admin/pt-settings/pt-settings-loader.php';

		//Admin_Helper::boot();
	}

	/**
	 * Load modules
	 */
	private function load_modules() {

		if ( bp_is_active( 'xprofile' ) && bugmapp_is_members_map_enabled() ) {
			//Members_Loader::boot();
		}

		if ( bp_is_active( 'groups' ) && bugmapp_is_groups_map_enabled() ) {
			//Groups_Loader::boot();
		}
	}

	/**
	 * Load translations.
	 */
	public function load_translations() {
		load_plugin_textdomain( 'mediapress', false, basename( dirname( mediapress()->get_basename() ) ) . '/languages' );
	}
}
