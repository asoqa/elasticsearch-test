<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/get.html
 * 说明:
 *   调用es restapi查询索引的各种方式
 * @author 	can.zhaoc
 *
 */
class TestUpdate extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	public static $id = 1;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "counter" : {"type" : "long", "store" : "yes"},
					"tags" : {"type" : "string", "store" : "no"},
		        },
				"_timestamp" : { "enabled" : true },
				"_ttl" : { "enabled" : true }					
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
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/" . self::$id;
		self::$ch = curl_init($url);
		
		$index = '{
		    "counter" : 1,
		    "tags" : ["red"]
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);		
		// TODO Auto-generated TestIndex::setUp()
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated TestIndex::tearDown()
		parent::tearDown ();
		self::$id++;
	}
	
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		$method = "DELETE";
		$url = "http://10.232.42.205/test";
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		
		$result = curl_exec(self::$ch);		
		
		$url = "http://10.232.42.205/test/index-child";
		curl_setopt(self::$ch, CURLOPT_URL, $url);		
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

	/**
	 * 说明：
	 * 	根据id查询索引
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回的json索引
	 */
	public function testIncreaseCounter() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/" . self::$id . "/_update";
		
		$query = '{
		    "script" : "ctx._source.counter += count",
		    "params" : {
		        "count" : 4
		    }
		}';

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"' . self::$id . '","_version":2}';
		$this->assertEquals($expected, $result, $result);	

		//断言更新内容是否正确
		$method = "GET";
		$url = "http://10.232.42.205/test/index/" . self::$id;
		curl_setopt(self::$ch, CURLOPT_URL, $url);	
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":2,"exists":true, "_source" : {"counter":5,"tags":["red"]}}';
		$this->assertEquals($expected, $result, $result);
	}
}

