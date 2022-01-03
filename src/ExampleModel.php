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

if ( ! class_exists( __NAMESPACE__ . '\ExampleModel' ) ) {
	/**
	 * DB Model.
	 *
	 * Interacts with DB for custom table. Table is expected to be named the same as the class. Alternatively,
	 * the class can implement `get_table_name` method as an override.
	 */
	class ExampleModel extends Model {

		/**
		 * Primary Key for table.
		 *
		 * @var string
		 */
		protected static $primary_key = 'id';

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
			global $wpdb;

			return "CONSTRAINT `fk_post` FOREIGN KEY (`post_id`) REFERENCES $wpdb->posts(`ID`) ON DELETE CASCADE ON UPDATE CASCADE";
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
			return [
				'id'         => 'bigint(20) NOT NULL AUTO_INCREMENT',
				'created_at' => "datetime NOT NULL DEFAULT '1970-01-01 00:00:01'",
				'token'      => "varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ''",
				'post_id'    => "bigint(20) unsigned NOT NULL",
				'data'       => "longtext COLLATE utf8mb4_unicode_ci NOT NULL",
			];
		}

	}
}