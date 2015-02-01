<?php

# sqlBuffer
class Sql_Buffer {
	private static $connected = false;
	private $query;
	private $result;
    private static $connection;
	
	public function __construct($name, $file, $line) {
		if (!self::$connected) {
			$conf = Config::$db;

            self::$connection = new mysqli($conf['host'], $conf['user'], $conf['pass'], $conf['base']);

            self::$connection->query("SET NAMES UTF8;");

//			mysql_connect($conf['host'], $conf['user'], $conf['pass']);
//			mysql_select_db($conf['base']);
//			mysql_query("SET NAMES UTF8;");
			self::$connected = true;
		}
		return $this;
	}
	
	public function embed($element, $query) {
		$this->query = $query;
		return $this;
	}
	
	public function execute() {
//		$this->result = mysql_query($this->query);
		$this->result = self::$connection->query($this->query);
		return $this;
	}
	
	public function fetchAssoc() {
		return $this->result->fetch_assoc();
	}
	
	public function insertId() {
        if (!empty($this->result->insert_id))
        {
		    return $this->result->insert_id;
        }
        else
        {
            return null;
        }
	}
}