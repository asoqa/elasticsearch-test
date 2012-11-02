<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * test case.
 */
class TestIndex extends PHPUnit_Framework_TestCase {
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"}
		        }
		    }
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $map);
		
		$result = curl_exec(self::$ch);		
	}
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated TestIndex::setUp()
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated TestIndex::tearDown()
		parent::tearDown ();
	}
	
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		$method = "DELETE";
		$url = "http://10.232.42.205/test/index";
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		
		$result = curl_exec(self::$ch);		
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
		self::$ch = curl_init();
	}
	
	public function _destruct() {
		curl_close(self::$ch);
	}
	
	public function testIndexWithId() {
		$method = "PUT";
		$id = 1;
		$url = "http://10.232.42.205/test/index/" . $id ;
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying out Elastic Search"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);		
		
		$result = curl_exec(self::$ch);
				
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"1","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}
}

