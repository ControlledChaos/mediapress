<?php
/**
 *
 * Short Description
 *
 * @package    BUGMAPP
 * @subpackage Models
 * @copyright  Copyright (c) 2018, Brajesh Singh
 * @license    https://www.gnu.org/licenses/gpl.html GNU Public License
 * @author     Brajesh Singh
 * @since      1.0.0
 */

namespace PressThemes\BUGMAPP\Models;

/**
 * Poor man's db model.
 */
abstract class Model {

	/**
	 * Primary key field.
	 *
	 * @var string
	 */
	protected static $primary_key = 'id';

	/**
	 * Enable timestamp.
	 *
	 * @var bool
	 */
	protected $timestamps = true;

	/**
	 * Created at field name.
	 */
	const FIELD_CREATED_AT = 'created_at';

	/**
	 * Updated at field name.
	 */
	const FIELD_UPDATED_AT = 'updated_at';

	/**
	 * Insert/Update the record.
	 *
	 * @return bool
	 */
	public function save() {
		global $wpdb;

		$schema = static::schema();

		$format = array();
		$data   = array();

		foreach ( $schema as $prop => $type ) {

			if ( $prop === static::$primary_key ) {
				continue;
			} elseif ( $this->timestamps && self::is_timestamp_field( $prop, $type ) ) {
				$pair = $this->get_timestamp_pair( $prop, $type );

				if ( $pair ) {
					$data[ $prop ] = $pair['value'];
					$format[]      = $pair['format'];
				}

				continue;
			}

			if ( property_exists( $this, $prop ) && ( $pair = $this->get_pair( $prop ) ) ) {
				$data[ $prop ] = $pair['value'];
				$format[]      = $pair['format'];
			}
		}

		$pk_name = static::$primary_key;
		$table   = static::table();

		// primary key value.
		$pk_value = empty( $this->{$pk_name} ) ? 0 : $this->{$pk_name};
		$pk_type  = $schema[ $pk_name ];

		if ( $pk_value ) {
			// it is an update.
			return $wpdb->update( $table, $data, array( $pk_name => $pk_value ), $format, array( self::format( $pk_type ) ) );
		}

		if ( false !== $wpdb->insert( $table, $data, $format ) ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Delete records
	 *
	 * @return bool|int
	 */
	public function delete() {
		if ( empty( $this->id ) ) {
			return false;
		}

		return static::destroy( array( 'id' => $this->id ) );
	}

	/**
	 * Create an entry into the database from the given args.
	 *
	 * @param array $args args.
	 *
	 * @return static
	 */
	public static function create( $args ) {
		$object = new static();

		$object->fill( $args );

		return $object;
	}

	/**
	 * Bulk update.
	 *
	 * @param array $args key=>val array.
	 * @param array $where_conditions key=>val array.
	 *
	 * @return bool|int
	 */
	public static function update( $args, $where_conditions ) {
		global $wpdb;

		$schema = static::schema();

		$format = array();
		$data   = array();

		// data & format.
		foreach ( $args as $prop => $value ) {
			$type = isset( $schema[ $prop ] ) ? $schema[ $prop ] : '';
			if ( ! $type ) {
				continue;
			}

			$data[ $prop ] = $value;
			$format[]      = self::format( $type );
		}

		$where        = array();
		$where_format = array();

		foreach ( $where_conditions as $prop => $value ) {
			$type = isset( $schema[ $prop ] ) ? $schema[ $prop ] : '';
			if ( ! $type ) {
				continue;
			}

			$where[ $prop ] = $value;
			$where_format[] = self::format( $type );
		}

		$table = static::table();

		// it is an update.
		return $wpdb->update( $table, $data, $where, $format, $where_format );
	}

	/**
	 * Delete one or more rows.
	 *
	 * @param array $args conditions.
	 *
	 * @return bool|int
	 */
	public static function destroy( $args ) {
		if ( empty( $args ) ) {
			return false;
		}

		global $wpdb;

		$table = static::table();

		$where_clause = static::build_where_clause( $args );

		if ( empty( $where_clause ) ) {
			return false;
		}

		return $wpdb->query( "DELETE FROM {$table} {$where_clause}" );
	}

	/**
	 * Find a row.
	 *
	 * @param int $id primary key value.
	 *
	 * @return static|null
	 */
	public static function find( $id ) {
		$table = static::table();
		global $wpdb;

		$schema = static::schema();

		$pk_name   = static::$primary_key;
		$pk_format = self::format( $schema[ $pk_name ] );
		$where_sql = $wpdb->prepare( " WHERE {$pk_name} = {$pk_format}", $id );

		$row = $wpdb->get_row( "SELECT * FROM {$table} {$where_sql}" );

		if ( is_null( $row ) ) {
			return null;
		}

		$object = new static();
		$object->fill( $row );

		return $object;
	}

	/**
	 * Get the first object or null.
	 *
	 * @param array $args args.
	 *
	 * @return static|null
	 */
	public static function first( $args ) {
		global $wpdb;
		$table = static::table();

		$where_sql = static::build_where_clause( $args );

		$args['orderby'] = empty( $args['orderby'] ) ? self::$primary_key : $args['orderby'];

		$orderby_clause = self::build_order_by_clause( $args );
		$paged_clause   = self::build_paged_clause( array( 'page' => 1, 'per_page' => 1 ) );

		$result = $wpdb->get_row( "SELECT * FROM {$table} {$where_sql} {$orderby_clause} {$paged_clause}" );

		if ( empty( $result ) ) {
			return null;
		}

		return static::to_object( $result );
	}

	/**
	 * Get the first object or null.
	 *
	 * @param array $args args.
	 *
	 * @return int
	 */
	public static function exists( $args ) {
		global $wpdb;
		$table = static::table();

		$where_sql = static::build_where_clause( $args );

		$args['orderby'] = empty( $args['orderby'] ) ? self::$primary_key : $args['orderby'];

		$orderby_clause = self::build_order_by_clause( $args );
		$paged_clause   = self::build_paged_clause( $args );

		return $wpdb->get_var( "SELECT COUNT('*') FROM {$table} {$where_sql} {$orderby_clause} {$paged_clause}" );
	}

	/**
	 * Get a collection of rows.
	 *
	 * @param array $args args.
	 *
	 * @return static[]
	 */
	public static function get( $args = array() ) {
		global $wpdb;
		$table = static::table();

		$where_sql = static::build_where_clause( $args );

		$orderby_clause = self::build_order_by_clause( $args );
		$paged_clause   = self::build_paged_clause( $args );

		$results = $wpdb->get_results( "SELECT * FROM {$table} {$where_sql} {$orderby_clause} {$paged_clause}" );

		if ( empty( $results ) ) {
			return array();
		}

		return array_map( 'self::to_object', $results );
	}

	/**
	 * Get all items.
	 *
	 * @return static[]
	 */
	public static function all() {
		return static::get();
	}

	/**
	 * Load properties from array or another props.
	 *
	 * @param array|object $args args.
	 */
	public function fill( $args ) {
		if ( is_array( $args ) ) {
			$this->from_array( $args );
		} elseif ( is_object( $args ) ) {
			$this->from_object( $args );
		}
	}

	/**
	 * Convert a row to the object.
	 *
	 * @param object|array $row object or array.
	 *
	 * @return static
	 */
	public static function to_object( $row ) {
		$object = new static();
		$object->fill( $row );

		return $object;
	}

	/**
	 * Schema for the table.
	 *
	 * Maps field to type.
	 *
	 * @return array
	 */
	abstract public static function schema();

	/**
	 * Table name.
	 *
	 * @return string
	 */
	abstract public static function table();

	/**
	 * Get the format for the given type.
	 *
	 * @param string $type type.
	 *
	 * @return mixed|string
	 */
	protected static function format( $type ) {
		$format = array(
			'integer' => '%d',
			'bool'    => '%d',
			'string'  => '%s',
			'float'   => '%f',
		);

		return isset( $format[ $type ] ) ? $format[ $type ] : '%s';
	}

	/**
	 * Is it primitive type?
	 *
	 * @param string $type type name.
	 *
	 * @return bool
	 */
	protected static function is_primitive( $type ) {
		return in_array( $type, array( 'integer', 'string', 'float', 'bool' ), true );
	}

	/**
	 * Check if the given field is a timestamp field.
	 *
	 * @param string $field field name.
	 * @param string $type field type.
	 *
	 * @return bool
	 */
	protected static function is_timestamp_field( $field, $type ) {
		$is = static::FIELD_CREATED_AT === $field || static::FIELD_UPDATED_AT === $field;

		return $is && ( 'datetime' === $type || 'timestamp' === $type );
	}

	/**
	 * Reserved fields.
	 *
	 * @return array
	 */
	protected static function reserved_fields() {
		return array( 'order', 'orderby', 'per_page', 'offset', 'page' );
	}

	/**
	 * Get the pair of value/format for the given property.
	 *
	 * @param string $prop name.
	 *
	 * @return array|bool
	 */
	protected function get_pair( $prop ) {
		$schema = static::schema();
		if ( ! isset( $schema[ $prop ] ) ) {
			return false;
		}

		$type  = $schema[ $prop ];
		$value = isset( $this->{$prop} ) ? $this->{$prop} : '';
		if ( ! $value && ! self::is_primitive( $type ) ) {
			return false;
		}

		return array(
			'value'  => $value,
			'format' => self::format( $type ),
		);
	}

	/**
	 * Get the pair of value/format for the given timestamp property.
	 *
	 * @param string $prop name.
	 *
	 * @return array|bool
	 */
	protected function get_timestamp_pair( $prop ) {
		$value  = null;
		$format = '%s';

		if ( ! $this->timestamps ) {
			return false;
		} elseif ( static::FIELD_UPDATED_AT === $prop ) {
			$value  = current_time( 'mysql' );
			$format = self::format( 'datetime' );
		} elseif ( static::FIELD_CREATED_AT === $prop ) {
			$value  = empty( $this->{$prop} ) ? current_time( 'mysql' ) : $this->{$prop};
			$format = self::format( 'datetime' );
		}

		return compact( 'value', 'format' );
	}

	/**
	 * Get fields which are time stamp.
	 *
	 * @return array
	 */
	protected static function get_timestamp_fields() {

		$fields = array();

		if ( static::FIELD_CREATED_AT ) {
			$fields[] = static::FIELD_CREATED_AT;
		}

		if ( static::FIELD_UPDATED_AT ) {
			$fields[] = static::FIELD_UPDATED_AT;
		}

		return $fields;
	}

	/**
	 * Get where conditions.
	 *
	 * @param array $args conditions.
	 * @param string $operator AND|OR.
	 *
	 * @return string
	 */
	protected static function build_where_clause( $args, $operator = 'AND' ) {
		global $wpdb;
		$props = static::schema();

		$reserved = self::reserved_fields();

		$where = array();

		foreach ( $args as $key => $value ) {
			if ( ! isset( $props[ $key ] ) || in_array( $key, $reserved ) ) {
				continue;
			}

			$op = '=';

			if ( is_array( $value ) ) {

				if ( isset( $value['op'] ) ) {
					$op    = $value['op'];
					$value = $value['value'];
				}
			}

			$format = self::format( $props[ $key ] );
			$clause = '';

			switch ( $op ) {

				case 'IN':
				case 'NOT IN':
					$clause = self::get_in_not_in_clause( $op, $key, $value );
					break;

				case 'BETWEEN' :
				case 'NOT BETWEEN' :
					$clause = is_array( $value ) ? $wpdb->prepare( "{$op} {$format} AND {$format}", $value[0], $value[1] ) : '';
					break;

				case 'LIKE' :
				case 'NOT LIKE' :
					$value  = '%' . $wpdb->esc_like( $value ) . '%';
					$clause = $wpdb->prepare( '%s', $value );
					break;
				default :
					$clause = $wpdb->prepare( "{$key} {$op} {$format}", $value );
					break;
			}

			if ( $clause ) {
				$where[ $key ] = $clause;
			}
		}

		return empty( $where ) ? '' : 'WHERE ' . join( ' ' . $operator . ' ', $where );
	}

	/**
	 * Get order by clause.
	 *
	 * @param array $args args.
	 *
	 * @return string
	 */
	protected static function build_order_by_clause( $args ) {
		if ( empty( $args['orderby'] ) ) {
			return '';
		}
		$schema = static::schema();

		$orderby_field = $args['orderby'];

		if ( ! isset( $schema[ $orderby_field ] ) ) {
			return '';
		}

		$order = empty( $args['order'] ) ? 'DESC' : $args['order'];
		global $wpdb;

		return $wpdb->prepare( 'ORDER BY %s %s', $orderby_field, $order );
	}

	/**
	 * Get sql clause for pagination.
	 *
	 * @param array $args pagination args.
	 *
	 * @return string
	 */
	protected static function build_paged_clause( $args ) {
		$default = array(
			'per_page' => 10,
			'page'     => 1,
			'offset'   => 0,
		);

		$args = wp_parse_args( $args, $default );

		$per_page = absint( $args['per_page'] );
		$page     = absint( $args['page'] );
		$offset   = absint( $args['offset'] );

		global $wpdb;

		if ( ! $offset && $page ) {
			$offset = ( $page - 1 ) * $per_page;
		}

		if ( $offset && $per_page ) {
			return $wpdb->prepare( 'LIMIT %d, %d', $offset, $per_page );
		}

		return '';
	}

	/**
	 * Get sql clause for IN/Not In operation.
	 *
	 * @param string $op 'IN', 'NOT IN'.
	 * @param string $field field name.
	 * @param string|array $values values.
	 *
	 * @return bool|string
	 */
	protected static function get_in_not_in_clause( $op, $field, $values ) {
		global $wpdb;

		// do we have commas?
		if ( ! is_array( $values ) ) {
			$values = explode( ',', $values );
		}

		// prepared values.
		$prepared = array();

		foreach ( $values as $value ) {
			$value = trim( $value );
			// Let the prepare do its job.
			$prepared[] = ( is_numeric( $value ) ) ? $wpdb->prepare( '%d', $value ) : $wpdb->prepare( '%s', $value );
		}

		if ( empty( $prepared ) ) {
			return false;
		}

		// IN/NOT IN.
		return sprintf( '%s %s ( %s )', trim( $field ), $op, implode( ',', $prepared ) );
	}

	/**
	 * Map object to class property
	 *
	 * @param object $args Object for property and its value.
	 */
	private function from_object( $args ) {
		$args = get_object_vars( $args );
		$this->from_array( $args );
	}

	/**
	 * Map array to class properties
	 *
	 * @param array $args Array property with value.
	 */
	private function from_array( $args ) {

		$schema = static::schema();

		foreach ( $args as $property => $value ) {

			if ( ! isset( $schema[ $property ] ) ) {
				continue;
			}

			$this->{$property} = $value;
		}
	}
}
