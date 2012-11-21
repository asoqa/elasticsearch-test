<?php

require_once 'PHPUnit\Framework\TestCase.php';

	/**
	 * elasticsearch.org:
	 * 	http://www.elasticsearch.org/guide/reference/api/delete.html
	 * 说明:
	 *   调用es restapi删除索引的各种方式，没有对索引内容正确性进行完整断言
	 * @author 	can.zhaoc
	 *
	 */	
class TestDelete extends PHPUnit_Framework_TestCase {
	public static $ch;
	public static $id = 1;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"}
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
		    "message" : "trying out Elastic Search"
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
	 * 	根据id删除索引
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回的json索引，索引是否真正被删除未做断言
	 */
	public function testDeleteById() {
		$method = "DELETE";
		$url = "http://10.232.42.205/test/index/" . self::$id ;
		self::$ch = curl_init($url);
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"found":true,"_index":"test","_type":"index","_id":"1","_version":2}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}
	
	/**
	 * 说明：
	 * 	根据id和version删除索引
	 * 前提：
	 * 	存在该id和version的索引
	 * 判断：
	 * 	1.	返回的json索引，索引是否真正被删除未做断言
	 */	
	public function testDeleteByVersion() {
		$method = "DELETE";
		$url = "http://10.232.42.205/test/index/" . self::$id . "?version=1";
		self::$ch = curl_init($url);
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"found":true,"_index":"test","_type":"index","_id":"' . self::$id . '","_version":2}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}	
	
	/**
	 * 说明：
	 * 	根据路由删除索引
	 * 前提：
	 * 	存在该id和路由的索引
	 * 判断：
	 * 	1.	返回的json索引，索引是否真正被删除未做断言
	 */
	public function testDeleteByRouting() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index?routing=test_routing" ;
		self::$ch = curl_init($url);
		
		$index = '{
			"user" : "test_routing",
		    "message" : "trying out Elastic Search, routing"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
	
		$result = curl_exec(self::$ch);
		
		//delete by routing
		$method = "DELETE";
		$url = "http://10.232.42.205/test/index?routing=test_routing" ;		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
	
		$result = curl_exec(self::$ch);
				
		$expected = '{"ok":true}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}	
	
	public function testDeleteParentChild() {
		
	}
}

