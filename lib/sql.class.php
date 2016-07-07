<?php

	class SQL {

		function __construct() {

		}

		public function db_connect() {
			// All good, start DB connect
			$mysqli = new mysqli(Config::dbHost(), Config::dbUser(), Config::dbPass(), Config::dbName());
			//
			if($mysqli->connect_errno) {
				Response::error(503, "503 Service Unavailable (DB connection failed).");
			}

			return $mysqli;
		}
	}