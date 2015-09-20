<?php

	require_once "MysqliDb/MysqliDb.php";

	class MysqliDbWrapper extends MysqliDb {
		public function __construct($db, $host = DB_HOST, $user = DB_USER, $pass = DB_PASS){
			parent::__construct($host,$user,$pass,$db);
			return $this;
		}
		public function rawQuerySingle(...$args){
			return $this->singleRow(parent::rawQuery(...$args));
		}
		public function singleRow($query){
			return empty($query[0]) ? null : $query[0];
		}
		public function whereEp($s, $e = null){
			if (!isset($e)){
				parent::where('season', intval($s['season']));
				parent::where('episode',intval($s['episode']));
			}
			else {
				parent::where('season', $s);
				parent::where('episode',$e);
			}
			return $this;
		}
		public function count($table){
			return parent::getOne($table, 'COUNT(*) as c')['c'];
		}
		public function orderByLiteral($orderstr, $direction = 'ASC'){
            $this->_orderBy[$orderstr] = $direction;
            return $this;
		}
	}
