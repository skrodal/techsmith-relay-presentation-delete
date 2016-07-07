<?php

	class Routes_DELETE extends Routes{

		function __construct() {
			parent::__construct();
		}
		/**
		 * Will remove the presentation record(s) from the table.
		 *
		 * Do this following a presentation restore (which is requested by user client by setting `undelete` to true).
		 */
		function deletePresentationRecordsFromTable(){
			$response = array();
			$issues = false;
			// Will exit on error
			$requestBody = Utils::getRequestBody();
			// Loop each org and save storage in db
			foreach($requestBody['presentations'] as $presentation) {
				$presentationPath = $this->sql()->real_escape_string($presentation['path']);
				// Run a preliminary query to get presentation details before delete (only so we have something to return)
				if($presToDelete = $this->sql()->query("SELECT * FROM $this->tableName WHERE path='$presentationPath'")->fetch_assoc()){
					$sql = "DELETE FROM $this->tableName WHERE path='$presentationPath'";
					// Exit on error
					if(!$result = $this->sql()->query($sql)) {
						Response::error(500, "500 Internal Server Error (DB DELETE failed): ". $this->sql()->error);//. $mysqli->error
					}
					array_push($response, $presToDelete);
				} else {
					array_push($response, array('path' => $presentationPath, 'error' => 'Not found in the table. Skipped.'));
					$issues = true;
				}


			}

			if(!$issues) {
				Response::result($response, "Presentation record(s) were successfully removed from the table.");
			} else {
				Response::result($response, "One or more presentation paths were not found in the table. See 'response' object for more info.");
			}

		}
		
	}