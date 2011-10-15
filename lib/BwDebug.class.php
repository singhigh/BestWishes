<?php
/**
 * Debug class, will be used to store some stats and SQL queries/errors when needed
 */
class BwDebug
{
	const NO_DEBUG = 0;
	const LOG_PERF = 1;
	const LOG_ALL  = 2;

	protected static $instance;

	private $mode;

	private $sqlLogs;
	private $sqlQueriesCount;

	public function __construct($mode = self::NO_DEBUG)
	{
		$this->mode = $mode;
		
		$this->sqlLogs         = array();
		$this->sqlQueriesCount = 0;
	}

	public static function getInstance()
	{
		if (!isset (self::$instance))
			self::$instance = new self;
		
		return self::$instance;
	}

	public function store($type = 'query', $toStore = '') {
		if($type = 'query') {
			$this->sqlLogs[] = $toStore;
			$this->sqlQueriesCount++;
		} else {
			$this->sqlLogs[] = $toStore;
		}
	}

	public static function storeQuery($query = '') {
		if(!empty($query)) {
			$debug = self::getInstance();
			$debug->store('query', $query);
			return true;
		}
		return false;
	}

	public static function storeError($error = '') {
		if(!empty($error)) {
			$debug = self::getInstance();
			$debug->store('error', $error);
			return true;
		}
		return false;
	}

	protected function getMode() {
		return $this->mode;
	}

	protected function setMode($mode = self::NO_DEBUG) {
		$this->mode = $mode;
		return true;
	}

	public static function getDebugMode() {
		$debug = self::getInstance();
		return $debug->getMode();
	}

	public static function setDebugMode($mode = self::NO_DEBUG) {
		$debug = self::getInstance();
		return $debug->setMode($mode);
	}
}