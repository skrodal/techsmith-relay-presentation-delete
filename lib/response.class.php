<?php

	class Response {

		public static function result($response, $info= false) {
			// Ensure no caching occurs on server (correct for HTTP/1.1)
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header("Expires: Fri, 10 Oct 1980 04:00:00 GMT"); // Date in the past
			// CORS
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Credentials: true");
			header("Access-Control-Allow-Methods: HEAD, GET, POST, OPTIONS");
			header("Access-Control-Allow-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
			header("Access-Control-Expose-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
			//
			header('content-type: application/json; charset=utf-8');
			//
			http_response_code(200);
			// Return response
			if(!$info) {
				exit(json_encode(array('status' => true, 'response' => $response)));
			} else {
				exit(json_encode(array('status' => true, 'info' => $info, 'presentations' => $response)));
			}
		}

		public static function error($code, $error) {
			// Ensure no caching occurs on server (correct for HTTP/1.1)
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header("Expires: Fri, 10 Oct 1980 04:00:00 GMT"); // Date in the past
			// CORS
			header("Access-Control-Allow-Origin: *");
			header("Access-Control-Allow-Credentials: true");
			header("Access-Control-Allow-Methods: HEAD, GET, POST, OPTIONS");
			header("Access-Control-Allow-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
			header("Access-Control-Expose-Headers: Authorization, X-Requested-With, Origin, Accept, Content-Type");
			//
			header('content-type: application/json; charset=utf-8');
			http_response_code($code);

			exit( json_encode(
				array(
					'status' => false,
					'message' => $error
				)
			));
		}
	}