<?php
	class Config {
		private static $config = null;

		public static function setDevMode($devMode) {
			if($devMode){
				$config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . "service-ecampus-no/etc/techsmith-relay/relay_mysql_presdelete_config_dev.js"), true);
			} else {
				$config = json_decode(file_get_contents("/var/www/etc/techsmith-relay/relay_mysql_presdelete_config.js"), true);
			}
		}

		public static function getConfig() {
			global $config;
			return $config;
		}

		public static function accessToken() {
			global $config;
			return $config['access_token'];
		}

		// If true, it enables some dev-routes
		public static function isDev() {
			global $config;
			return $config['is_dev'];
		}

		public static function dbHost() {
			global $config;
			return $config['db_host'];
		}

		public static function dbName() {
			global $config;
			return $config['db_name'];
		}

		public static function dbTableName() {
			global $config;
			return $config['db_table_name'];
		}

		public static function dbUser() {
			global $config;
			return $config['db_user'];
		}

		public static function dbPass() {
			global $config;
			return $config['db_pass'];
		}

		public static function daysToKeep() {
			global $config;
			return $config['days_to_keep'];
		}
	}