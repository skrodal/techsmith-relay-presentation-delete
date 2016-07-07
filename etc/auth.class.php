<?php

	class Auth {
		
		public static function checkToken() {
			// Check header for token that equals that defined for this service
			if($_SERVER['HTTP_TOKEN'] !== Config::accessToken()) {
				Response::error(401, "Unauthorized");
			}
		}
	}