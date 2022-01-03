<?php
/**
 * DB Class.
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
 * @category   WPS\WP
 * @package    WPS\WP\DB
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2022 Travis Smith
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU Public License v2
 * @link       https://wpsmith.net/
 * @since      0.0.1
 */

namespace WPS\WP\DB;

use WPS\Core\Singleton;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( __NAMESPACE__ . '\DB' ) ) {
	/**
	 * Class DB
	 * @package WPS\WP\DB
	 */
	abstract class DB extends Singleton {

		/**
		 * Plugin version option name.
		 *
		 * @var string
		 */
		public static string $plugin_version_option = 'plugin_version';

		/**
		 * DB name.
		 *
		 * @var string
		 */
		protected static string $table_name = 'table_name';

		/**
		 * Constructor.
		 *
		 * @param null $args
		 */
		public function __construct( $args = null ) {
			parent::__construct( $args );

			\add_action( 'init', [ $this, 'register_table' ], 1 );
			\add_action( 'switch_blog', [ $this, 'register_table' ] );
		}

		/**
		 * Registers table.
		 */
		public function register_table() {
			global $wpdb;

			$wpdb->transactions = $wpdb->prefix . static::$table_name;
		}

		/**
		 * Gets the transactions DB name.
		 *
		 * @return string
		 */
		public static function table(): string {
			global $wpdb;

			return $wpdb->prefix . static::$table_name;
		}

		/**
		 * Adds plugin version to the DB.
		 */
		public static function add_version() {
			\add_option( DB::$plugin_version_option, self::get_version() );
		}

		/**
		 * Updates plugin version in the DB.
		 *
		 * @param string $version Semantic version number.
		 */
		public static function update_version( string $version ) {
			\update_option( static::$plugin_version_option, $version );
		}

		/**
		 * Gets plugin version from the DB.
		 *
		 * @return string Current semantic version number.
		 */
		public static function get_version(): string {
			return \get_option( self::$plugin_version_option, '0.0.0' );
		}

		/**
		 * Creates the DB Table.
		 */
		public static function create_table(): array {
			global $wpdb;

			$charset_collate = $wpdb->get_charset_collate();
			$table_name      = static::table();
			$instance        = static::get_instance();

			$sql = "CREATE TABLE $table_name (";
			foreach ( $instance->get_fields() as $field => $def ) {
				$sql .= "	`$field` $def,\n";
			}
			$sql .= "	UNIQUE KEY `id` (id),
	CONSTRAINT `fk_post` FOREIGN KEY (`post_id`) REFERENCES $wpdb->posts(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `fk_solution` FOREIGN KEY (`solution_id`) REFERENCES $wpdb->posts(`ID`) ON DELETE CASCADE ON UPDATE CASCADE  
) $charset_collate;
";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			return dbDelta( $sql );
		}

		/**
		 * Upgrade stuffs.
		 */
		abstract public function upgrade();

	}
}
