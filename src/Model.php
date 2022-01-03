<?php
/**
 * DB Model Class.
 *
 * Handles DB interactions for transactions.
 *
 * You may copy, distribute and modify the software as long as you track
 * changes/dates in source files. Any modifications to or software including
 * (via compiler) GPL-licensed code must also be made available under the GPL
 * along with build & install instructions.
 *
 * PHP Version 7.4
 *
 * @category   CaseHero/Admin
 * @package    CaseHero
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2021 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://wpsmith.net/
 * @since      0.0.1
 */

namespace WPS\WP\DB;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\Model' ) ) {
	/**
	 * DB Model.
	 *
	 * Interacts with DB for custom table. Table is expected to be named the same as the class. Alternatively,
	 * the class can implement `get_table_name` method as an override.
	 */
	abstract class Model {

		/**
		 * Primary Key for table.
		 *
		 * @var string
		 */
		protected static $primary_key = 'id';

		/**
		 * Gets the table name.
		 *
		 * @acces private
		 * @return string
		 */
		private static function _table() {
			global $wpdb;

			$cls = get_called_class();
			if ( method_exists( $cls, 'get_table_name' ) ) {
				$tablename = $cls::get_table_name();
			} else {
				$tablename = strtolower( get_called_class() );
				$tablename = explode( '\\', $tablename );
				$tablename = $tablename[ count( $tablename ) - 1 ];
			}

			return $wpdb->prefix . $tablename;
		}

		/**
		 * Gets the SQL string after running through wpdb->prepare.
		 *
		 * @param string $value Value of primary key.
		 *
		 * @return string|void
		 */
		private static function _fetch_sql( $value ) {
			global $wpdb;
			$sql = sprintf( 'SELECT * FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

			return $wpdb->prepare( $sql, $value );
		}

		/**
		 * Data to validate.
		 *
		 * Data should be sanitized before being passed into this function.
		 *
		 * @param array $data Data array.
		 *
		 * @return bool
		 */
		public static function valid_check( array $data ): bool {
			global $wpdb;

			$sql_where       = '';
			$sql_where_count = count( $data );
			$i               = 1;
			foreach ( $data as $key => $row ) {
				if ( $i < $sql_where_count ) {
					$sql_where .= "`$key` = '$row' and ";
				} else {
					$sql_where .= "`$key` = '$row'";
				}
				$i ++;
			}
			$sql     = 'SELECT * FROM ' . self::_table() . " WHERE $sql_where";
			$results = $wpdb->get_results( $sql );
			if ( count( $results ) != 0 ) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Primary key value to get from DB.
		 *
		 * @param string $value Value to get from DB table.
		 *
		 * @return array|object|void|null
		 */
		public static function get( $value ) {
			global $wpdb;

			return $wpdb->get_row( self::_fetch_sql( $value ) );
		}

		/**
		 * Inserts data into table.
		 *
		 * @param array $data Data to insert (in column => value pairs).
		 *                    Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                    Sending a null value will cause the column to be set to NULL - the corresponding
		 *                    format is ignored in this case.
		 *
		 * @return bool|int
		 */
		public static function insert( $data ) {
			global $wpdb;

			return $wpdb->insert( self::_table(), $data );
		}

		/**
		 * Updates an item in the table.
		 *
		 * @param array $data Data to update (in column => value pairs).
		 *                     Both $data columns and $data values should be "raw" (neither should be SQL escaped).
		 *                     Sending a null value will cause the column to be set to NULL - the corresponding
		 *                     format is ignored in this case.
		 * @param array $where A named array of WHERE clauses (in column => value pairs).
		 *                     Multiple clauses will be joined with ANDs.
		 *                     Both $where columns and $where values should be "raw".
		 *                     Sending a null value will create an IS NULL comparison - the corresponding
		 *                     format will be ignored in this case.
		 *
		 * @return bool|int
		 */
		public static function update( $data, $where ) {
			global $wpdb;

			return $wpdb->update( self::_table(), $data, $where );
		}

		/**
		 * Deletes a row from table by primary key value.
		 *
		 * @param string $value Primary key value.
		 *
		 * @return bool|int
		 */
		public static function delete( $value ) {
			global $wpdb;
			$sql = sprintf( 'DELETE FROM %s WHERE %s = %%s', self::_table(), static::$primary_key );

			return $wpdb->query( $wpdb->prepare( $sql, $value ) );
		}

		/**
		 * Gets insert ID.
		 *
		 * @return int
		 */
		public static function insert_id(): int {
			global $wpdb;

			return $wpdb->insert_id;
		}

		/**
		 * Changes timestamp to date string.
		 *
		 * @param ?int $time Timestamp.
		 *
		 * @return false|string
		 */
		public static function time_to_date( $time ) {
			return gmdate( 'Y-m-d H:i:s', $time );
		}

		/**
		 * Gets current time.
		 *
		 * @return false|string
		 */
		public static function now() {
			return self::time_to_date( time() );
		}

		/**
		 * Converts datetime string (adding GMT) to epoch timestamp.
		 *
		 * @param string $date Datetime string.
		 *
		 * @return false|int
		 */
		public static function date_to_time( $date ) {
			return strtotime( $date . ' GMT' );
		}

		/**
		 * Creates the DB Table.
		 *
		 * @param array $fields Array of fields as field_name => column definition.
		 *                      Example: [
		 *                          'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
		 *                          'created_at' => "datetime NOT NULL DEFAULT '1970-01-01 00:00:01'",
		 *                          'token'      => "varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''",
		 *                          'post_id'    => "bigint(20) unsigned NOT NULL",
		 *                          'data'       => "longtext COLLATE utf8mb4_unicode_ci NOT NULL",
		 *                      ]
		 *
		 * @param string $constraints Constraints SQL statement.
		 *                      Example: From the fields above, 'post_id' can be used with a constraint.
		 *                          CONSTRAINT `fk_post` FOREIGN KEY (`post_id`) REFERENCES $wpdb->posts(`ID`) ON DELETE CASCADE ON UPDATE CASCADE
		 *
		 * @return array|\WP_Error
		 */
		public function create_table( array $fields = [], string $constraints = '' ): \WP_Error|array {
			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();
			$table_name      = self::_table();
			$constraints     = '' == $constraints && method_exists( $this, 'get_constraints' ) ? $this->get_constraints() : $constraints;
			$fields          = [] == $fields && method_exists( $this, 'get_fields' ) ? $this->get_fields() : $fields;

			if ( empty( $fields ) ) {
				return new \WP_Error( 'no-fields', \__( 'Invalid fields', 'wps' ), $fields );
			}
			$sql = "CREATE TABLE $table_name (";
			foreach ( $fields as $field => $def ) {
				$sql .= "	`$field` $def,\n";
			}
			$sql .= '	UNIQUE KEY `' . static::$primary_key . '` (' . static::$primary_key . '),';
			$sql .= "
	$constraints
) $charset_collate;
";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			return dbDelta( $sql );
		}

		/**
		 * Gets the constraints.
		 *
		 * Example (assuming there is a key/column called `post_id`):
		 *  global $wpdb;
		 *
		 *  return "CONSTRAINT `fk_post` FOREIGN KEY (`post_id`) REFERENCES $wpdb->posts(`ID`) ON DELETE CASCADE ON UPDATE CASCADE";
		 * @return string Foreign key constraints.
		 */
		public function get_constraints(): string {
			return '';
		}

		/**
		 * Gets an array of fields as field_name => column definition.
		 *      Example: [
		 *          'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
		 *          'created_at' => "datetime NOT NULL DEFAULT '1970-01-01 00:00:01'",
		 *          'token'      => "varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''",
		 *          'post_id'    => "bigint(20) unsigned NOT NULL",
		 *          'data'       => "longtext COLLATE utf8mb4_unicode_ci NOT NULL",
		 *      ]
		 *
		 * @return array
		 */
		public function get_fields(): array {
			return [];
		}

	}
}