<?php

	class Routes_GET extends Routes{
		function __construct() {
			parent::__construct();
		}

		/**
		 * All presentations in table, regardless of deleted state.
		 */
		public function getAllPresentations() {
			$response = array();
			// Get all presentations with a timestamp older than 14 days that are not already deleted.
			$sql    = "SELECT * FROM $this->tableName";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}
			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "ALL presentations in table (inc. moved/deleted ones).");
		}

		public function getMovedPresentations() {
			$response = array();
			// Get all presentations that are marked as moved
			$sql    = "SELECT * FROM $this->tableName WHERE moved = 1";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}
			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "Presentations marked as already moved.");
		}

		/**
		 * Presentations scheduled to be moved
		 */
		public function getPresentationsToBeMoved() {
			$response = array();
			// Get all presentations that has not yet been moved
			$sql    = "SELECT * FROM $this->tableName WHERE moved <> 1 AND deleted <> 1";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}

			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "Presentations ready to be moved from user folder.");
		}

		public function getDeletedPresentations() {
			$response = array();
			// Get all presentations that are marked as moved
			$sql    = "SELECT * FROM $this->tableName WHERE deleted = 1";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}
			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "Presentations marked as deleted from the file system");
		}

		/**
		 * Presentations older than x days and ready to be deleted permanently
		 */
		public function getPresentationsToBeDeleted() {
			$response = array();
			// Get all presentations with a timestamp older than 14 days that are not already deleted.
			$sql    = "SELECT * FROM $this->tableName WHERE timestamp < DATE_SUB(NOW(), INTERVAL $this->daysToKeep DAY) AND moved = 1 AND deleted <> 1";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}

			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "Presentations ready to be deleted from the file system (i.e. older than $this->daysToKeep days.)");
		}


		/**
		 * Presentations that has been moved (==1), but not yet permanently deleted (!== 1) that the user has
		 * asked be moved back (undelete == 1).
		 */
		public function getPresentationsToBeUndeleted() {
			$response = array();
			// Get all presentations moved, but not yet deleted, that is marked for undelete.
			$sql    = "SELECT * FROM $this->tableName WHERE moved = 1 AND deleted <> 1 AND undelete = 1";
			$result = $this->sql()->query($sql);
			// Exit on error
			if(!$result) {
				Response::error(500, "500 Internal Server Error (DB query failed)."); //. $mysqli->error
			}

			// Loop returned rows and create a response
			while($row = $result->fetch_assoc()) {
				array_push($response, $row);
			}
			Response::result($response, "Presentations to be undeleted from the file system, i.e. moved back to its original location ('path'). When the presentation(s) are restored, send a DELETE request to '/presentations/delete/' with the returned response from this query to remove the entry from the DB.");
		}



		/**
		 *
		 */
		public function restoreSampleData() {
			if(Config::isDev()) {
				// Note: will not complain about presentations with the 'deleted' flag already set
				$query = "DROP TABLE IF EXISTS $this->tableName";
				// Exit on error
				if(!$result = $this->sql()->query($query)) {
					Response::error(500, "500 Internal Server Error (DB INSERT failed): " . $this->sql()->error);
				}

				$query = "CREATE TABLE $this->tableName (
							  id int(11) NOT NULL AUTO_INCREMENT,
					          timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
					          path text NOT NULL,
					          username varchar(40) NOT NULL DEFAULT '',
					          moved tinyint(1) unsigned NOT NULL DEFAULT 0,
					          deleted tinyint(1) unsigned NOT NULL DEFAULT 0,
					          undelete tinyint(1) unsigned NOT NULL DEFAULT 0,
					          presId int(11) NOT NULL,
					          userId int(11) NOT NULL,
					          PRIMARY KEY (id),
  							  UNIQUE KEY presId (presId)
			        	) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8";

				// Exit on error
				if(!$result = $this->sql()->query($query)) {
					Response::error(500, "500 Internal Server Error (DB INSERT failed): " . $this->sql()->error);
				}

				// Sample data where:
				// - records 1,2,3 represent presentations that are 14 days or older and have thus been both moved and deleted
				// - record 4 is moved (but not deleted) and a request from user to undelete has been recorded
				// - record 5 has recently been requested to be deleted and has not yet been moved (and cannot be undeleted - user can still cancel in UI)
				$query = "INSERT INTO $this->tableName (timestamp, path, username, moved, deleted, undelete)
					VALUES
					(NOW() - INTERVAL 64 DAY, 'ansatt/simonuninett.no/2016/08.04/12303/', 'simon@uninett.no', 1, 1, 0, 1231, 509),	
					(NOW() - INTERVAL 28 DAY, 'ansatt/simon@uninett.no/2014/03.04/216713/', 'simon@uninett.no', 1, 1, 0, 1232, 509),
					(NOW() - INTERVAL 14 DAY, 'ansatt/simonuninett.no/2014/11.06/186700/', 'simon@uninett.no', 1, 0, 0, 1233, 509),
					(NOW() - INTERVAL 7 DAY, 'ansatt/simonuninett.no/2015/12.04/116700/', 'simon@uninett.no', 1, 0, 1, 1234, 509),
					(NOW() - INTERVAL 1 HOUR, 'ansatt/simonuninett.no/2014/27.09/10333/', 'simon@uninett.no', 0, 0, 0, 1235, 509)
				";


				// Exit on error
				if(!$result = $this->sql()->query($query)) {
					Response::error(500, "500 Internal Server Error (DB INSERT failed): " . $this->sql()->error);
				}

				Response::result("Ok!");
			}

			Response::error(500, "Something went wrong!");
		}
	}