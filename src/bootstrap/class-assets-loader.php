<?php
/**
 * Assets Loader
 *
 * @package    MediaPress
 * @subpackage Bootstrap
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace PressThemes\MediaPress\Bootstrap;

// Exit if file accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 0 );
}

/**
 * Assets Loader.
 */
class Assets_Loader {

	/**
	 * Data to be send as localized js.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Boot it.
	 */
	public static function boot() {
		$self = new self();
		$self->setup();
	}

	/**
	 * Callbacks to various hooks
	 */
	public function setup() {
		add_action( 'bp_enqueue_scripts', array( $this, 'load_assets' ) );
		add_action( 'bp_admin_enqueue_scripts', array( $this, 'load_assets' ) );
	}

	/**
	 * Load plugin assets
	 */
	public function load_assets() {
		$this->register();
		$this->enqueue();
	}

	/**
	 * Register assets.
	 */
	public function register() {
		$this->register_vendors();
		$this->register_core();
	}

	/**
	 * Register vendor scripts.
	 */
	private function register_vendors() {
		$api_key = bugmapp_get_option( 'api_key' );
		wp_register_script( 'bugmapp_map_lib', "https://maps.googleapis.com/maps/api/js?key={$api_key}&libraries=places" );
	}

	/**
	 * Register core assets.
	 */
	private function register_core() {

		$url     = bugmapp()->get_url();
		$version = bugmapp()->version;

		wp_register_style( 'bugmapp_css', $url . 'assets/css/bugmapp.css', array(), $version );

		wp_register_script( 'bugmapp_js', $url . 'assets/js/bugmapp.js', array(
			'jquery',
			'bugmapp_map_lib',
		), $version );

		$this->data = array(
			'mapOptions'  => bugmapp_get_map_js_options(),
			'isMemberDir' => bp_is_members_directory(),
			'isGroupDir'  => bp_is_groups_directory(),
		);

		if ( function_exists( 'bugmapp_get_mapped_fields' ) ) {
			$mapped_fields = bugmapp_get_mapped_fields();
			$mapped_fields = ( $mapped_fields ) ? $mapped_fields : array();

			$this->data['mappedFields'] = $mapped_fields;
		}
	}

	/**
	 * Load assets.
	 */
	public function enqueue() {

		if ( ! $this->needs_loading() ) {
			return;
		}

		wp_enqueue_style( 'bugmapp_css' );
		wp_enqueue_script( 'bugmapp_js' );

		wp_localize_script( 'bugmapp_js', 'BugMapOpt', $this->data );
	}

	/**
	 * Check should we need to load plugin assets or not
	 */
	private function needs_loading() {

		if ( is_admin() && isset( $_GET['page'] ) &&
		     in_array( $_GET['page'], array( 'bp-groups', 'bp-profile-edit', 'bp-profile-setup' ) ) ) {
			return true;
		} elseif ( bp_is_groups_directory() || bp_is_members_directory() ) {
			return true;
		} elseif ( bp_is_user() && ( bp_is_profile_component() ) ) {
			return true;
		} elseif ( bp_is_group() ) {
			return true;
		}

		return false;
	}
}
