<?php
	require_once 'lib/response.class.php';
	require_once 'etc/config.class.php';
	require_once 'etc/auth.class.php';
	require_once 'lib/utils.class.php';
	require_once 'lib/router.class.php';

	// Edit this to false when going live
	Config::setDevMode(false);
	// GET/POST
	Utils::sanitizeInput();

	// Auth - will exit if missing/incorrect token
	Auth::checkToken();
	// Init
	$router = new Router();
	//$router->declareRoutes();
	$router->matchRoutes();