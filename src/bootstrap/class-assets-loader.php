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
		add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );
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
		// Register vendor libraries
	}

	/**
	 * Register core assets.
	 */
	private function register_core() {
		// register core assets files
	}

	/**
	 * Load assets.
	 */
	public function enqueue() {

	}

	/**
	 * Check should we need to load plugin assets or not
	 */
	private function needs_loading() {
	}
}
