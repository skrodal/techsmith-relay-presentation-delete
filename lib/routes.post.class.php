<?php

	class Routes_POST extends Routes {

		function __construct() {
			parent::__construct();
		}

		/**
		 *
		 */
		public function movePresentations() {

			$response = array();
			$issues = false;
			// Will exit on error
			$requestBody = Utils::getRequestBody();

			// Loop each org and save storage in db
			foreach($requestBody['presentations'] as $presentation) {
				$presentationPath = $this->sql()->real_escape_string($presentation['path']);
				// Note: will not complain about presentations with the `deleted` flag already set
				$query = "UPDATE $this->tableName SET moved=1 WHERE path='$presentationPath'";
				// Exit on error
				if(!$result = $this->sql()->query($query)) {
					Response::error(500, "500 Internal Server Error (DB INSERT failed): " . $this->sql()->error);
				}

				// See if presentation is in table before we continue
				if($presToBeMoved = $this->sql()->query("SELECT * FROM $this->tableName WHERE path='$presentationPath'")->fetch_assoc()){
					array_push($response, $presToBeMoved);
				} else {
					$issues = true;
					array_push($response, array('path' => $presentationPath, 'error' => 'Not found in the table. Skipped.'));
				}
			}

			if(!$issues) {
				Response::result($response, "Presentations were successfully marked as moved.");
			} else {
				Response::result($response, "One or more presentation Paths were not found in the table. See 'response' object for more info.");
			}

		}

		/**
		 *
		 */
		public function deletePresentations(){
			$response = array();
			$issues = false;
			// Will exit on error
			$requestBody = Utils::getRequestBody();

			// Loop each org and save storage in db
			foreach($requestBody['presentations'] as $presentation) {
				$presentationPath = $this->sql()->real_escape_string($presentation['path']);
				$query = "UPDATE $this->tableName SET deleted=1 WHERE path='$presentationPath'";
				// Exit on error
				if(!$result = $this->sql()->query($query)) {
					Response::error(500, "500 Internal Server Error (DB INSERT failed): " . $this->sql()->error);
				}

				// See if presentation is in table before we continue
				if($presToBeDeleted = $this->sql()->query("SELECT * FROM $this->tableName WHERE path='$presentationPath'")->fetch_assoc()){
					array_push($response, $presToBeDeleted);
				} else {
					$issues = true;
					array_push($response, array('path' => $presentationPath, 'error' => 'Not found in the table. Skipped.'));
				}
			}

			if(!$issues) {
				Response::result($response, "Presentation(s) were successfully marked as permanently deleted.");
			} else {
				Response::result($response, "One or more presentation Paths were not found in the table. See 'response' object for more info.");
			}
		}
	}