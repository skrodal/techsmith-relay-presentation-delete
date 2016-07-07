<?php
	require_once 'vendor/altorouter.class.php';
	require_once 'lib/sql.class.php';
	require_once 'lib/routes.class.php';


	class Router {

		private $router, $routes, $routes_get, $routes_post, $routes_delete, $routes_patch;

		public function __construct() {
			// ROUTER
			$this->router = new AltoRouter();
			$this->router->setBasePath(dirname($_SERVER['SCRIPT_NAME']));
			// ROUTES implementations
			$this->router->map('GET', '/', function () {
				Response::result($this->router->getRoutes());
			}, 'Get all routes available');

			$routes = new Routes();
			//
			switch($_SERVER['REQUEST_METHOD']) {
				case 'GET':
					require_once 'lib/routes.get.class.php';
					$this->routes_get = new Routes_GET();
					$this->declareGetRoutes();
					break;
				case 'POST':
					require_once 'lib/routes.post.class.php';
					$this->routes_post = new Routes_POST();
					$this->declarePostRoutes();
					break;
				case 'DELETE':
					require_once 'lib/routes.delete.class.php';
					$this->routes_delete = new Routes_DELETE();
					$this->declareDeleteRoutes();
					break;
				default:
					Response::error(404, "Unexpected HTTP method: " . $_SERVER['REQUEST_METHOD']);
			}

		}


		/**
		 * Declare all GET (READ) routes
		 *
		 * @throws Exception
		 */
		private function declareGetRoutes() {
			//
			$this->router->map('GET', '/dev/restore/', function () {
				Response::result($this->routes_get->restoreSampleData());
			}, 'FOR DEVELOPMENT: Drops table and creates sample records.');
			//
			$this->router->map('GET', '/presentations/all/', function () {
				Response::result($this->routes_get->getAllPresentations());
			}, 'Get ALL presentations in table (inc. deleted/moved).');
			//
			$this->router->map('GET', '/presentations/movable/', function () {
				Response::result($this->routes_get->getPresentationsToBeMoved());
			}, 'Get all presentations scheduled for removal.');
			//
			$this->router->map('GET', '/presentations/moved/', function () {
				Response::result($this->routes_get->getMovedPresentations());
			}, 'Get all presentations marked as moved.');
			//
			$this->router->map('GET', '/presentations/deletable/', function () {
				Response::result($this->routes_get->getPresentationsToBeDeleted());
			}, 'Get all presentations scheduled for permanent deletion.');
			//
			$this->router->map('GET', '/presentations/deleted/', function () {
				Response::result($this->routes_get->getDeletedPresentations());
			}, 'Get all presentations marked as deleted.');
			//
			$this->router->map('GET', '/presentations/undeletable/', function () {
				Response::result($this->routes_get->getPresentationsToBeUndeleted());
			}, 'Get all presentations requested to be UNdeleted.');
		}

		/**
		 * POST (WRITE) routes for turning moved/deleted flags on.
		 *
		 * @throws Exception
		 */
		private function declarePostRoutes() {
			$this->router->map('POST', '/presentations/update/move/', function () {
				Response::result($this->routes_post->movePresentations());
			}, 'Mark presentations as moved from user forlder.');

			$this->router->map('POST', '/presentations/update/delete/', function () {
				Response::result($this->routes_post->deletePresentations());
			}, 'Mark presentation(s) as permanently deleted from file server.');
		}

		/**
		 * Deletes one or more presentations (defined in request body) from the table.
		 *
		 * @throws Exception
		 */
		private function declareDeleteRoutes() {
			$this->router->map('DELETE', '/records/delete/', function () {
				Response::result($this->routes_delete->deletePresentationRecordsFromTable());
			}, 'Delete presentation record(s) from the table. Use when presentations were restored (undeleted) from backup and moved back to user folder.');
		}


		public function matchRoutes() {
			// Match current request
			$match = $this->router->match();
			//
			if($match && is_callable($match['target'])) {
				call_user_func_array($match['target'], $match['params']);
			} else {
				Response::error(404, $_SERVER["SERVER_PROTOCOL"] . " The requested resource could not be found.");
			}
		}
	}