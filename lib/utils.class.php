<?php

	class Utils {

		/**
		 * http://stackoverflow.com/questions/4861053/php-sanitize-values-of-a-array/4861211#4861211
		 */
		public static function sanitizeInput() {
			$_GET  = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);
			$_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		}

		public static function getRequestBody(){
			$requestBody = json_decode(file_get_contents('php://input'), true);
			// No presentation content in the request body
			if(!$requestBody['presentations'] || empty($requestBody['presentations'])) {
				Response::error(400, "400 No Content.");
			}
			return $requestBody;
		}
	}