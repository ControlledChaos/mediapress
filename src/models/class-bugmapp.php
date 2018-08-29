<?php
/**
 * Model class for bugmapp table
 *
 * @package    BUGMAPP
 * @subpackage Models
 */

namespace PressThemes\BUGMAPP\Models;

// Exit if accessed directly
if ( ! defined( 'ABSPATH') ) {
	exit;
}

use PressThemes\BUGMAPP\Schema\Schema;

/**
 * Class BUGMAPP
 *
 * @property-read int    $id        Row id.
 * @property-read int    $item_id   Item id.
 * @property-read string $item_type Item type.
 * @property-read int    $field_id  Field id.
 * @property-read string $lat       Latitude value.
 * @property-read string $lng       Longitude value.
 */
class BUGMAPP extends Model {

	/**
	 * Table name.
	 *
	 * @return string
	 */
	public static function table() {
		return Schema::table( 'bugmapp' );
	}

	/**
	 * Table schema.
	 *
	 * @return array
	 */
	public static function schema() {
		return array(
			'id'        => 'integer',
			'item_id'   => 'integer',
			'item_type' => 'string',
			'field_id'  => 'integer',
			'lat'       => 'string',
			'lng'       => 'string',
		);
	}
}
