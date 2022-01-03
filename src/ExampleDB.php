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
 * @category   CaseHero/Admin
 * @package    CaseHero
 * @author     Travis Smith <t@wpsmith.net>
 * @copyright  2021 Travis Smith
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

if ( ! class_exists( __NAMESPACE__ . '\ExampleDB' ) ) {
	/**
	 * Class ExampleDB
	 * @package CaseHero\Admin
	 */
	class ExampleDB extends DB {

		/**
		 * Plugin version option name.
		 *
		 * @var string
		 */
		public static string $plugin_version_option = 'my_plugin_version';

		/**
		 * DB table name.
		 *
		 * @var string
		 */
		protected static string $table_name = 'my_custom_table';

		/**
		 * Upgrade stuffs.
		 */
		public function upgrade() {
			self::upgrade_100();
			self::upgrade_200();

			// Adds version number to DB.
			update_option( DB::$plugin_version_option, '2.0.0' );
		}

		/**
		 * Upgrade to version 1.0.0.
		 */
		public static function upgrade_100() {
			if ( version_compare( '1.0.0', self::get_version() ) >= 0 ) {
				// Do something.
				self::update_version( '1.0.0' );
			}
		}

		/**
		 * Upgrade to version 2.0.0.
		 */
		public static function upgrade_200() {
			if ( version_compare( '2.0.0', self::get_version() ) >= 0 ) {
				$instance = self::get_instance();
				$instance->create_db_table();
				self::update_version( '2.0.0' );
			}
		}
	}
}
