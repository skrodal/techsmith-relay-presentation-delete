<?php

	class Routes {
		private $sql;
		protected $tableName, $daysToKeep;

		public function __construct() {
			// Connect to DB
			$sqlConn   = new SQL();
			$this->sql = $sqlConn->db_connect();
			$this->tableName = Config::dbTableName();
			$this->daysToKeep = Config::daysToKeep();
		}

		protected function sql(){
			return $this->sql;
		}
	}