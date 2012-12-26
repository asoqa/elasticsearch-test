<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/search.html
 * 说明:
 *   调用es restapi查询索引的各种方式
 * @author 	can.zhaoc
 *
 */
class TestCountAPI extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		//建索引库
		$method = "PUT";
		$url = "http://10.232.42.205/test";		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);

		//定义map
		$url = "http://10.232.42.205/test/index/_mapping";
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"},
                    "tag" : {"type" : "string", "store" : "no"}
		        },
				"_timestamp" : { "enabled" : true },
				"_ttl" : { "enabled" : true }					
		    }
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $map);
		$result = curl_exec(self::$ch);		
		
		//准备测试数据
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/1";
		$index = '{
		    "message" : "something blue",
		    "tag" : "blue"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		$result = curl_exec(self::$ch);
		
		$url = "http://10.232.42.205/test/index/2";
		$index = '{
		    "message" : "something green",
		    "tag" : "green"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		$result = curl_exec(self::$ch);
		
		self::$ch = curl_init();
		$method = "POST";
		$url = "http://10.232.42.205/test/_refresh";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
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
	 * 	统计结果数量，可以跨index和type查询
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testCount() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_count";
		
		$query = '{ 
		    "term" : { "message" : "something" } 
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"count":2,"_shards":{"total":5,"successful":5,"failed":0}}';
		$this->assertEquals($expected, $result, $result);	
	}
	
	/**
	 * 说明：
	 *  跨index和type查询，这里跨type
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testCountAcrossIndices() {
		$method = "GET";
		$url = "http://10.232.42.205/test/_count?query=message:something";
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
	
		$expected = '{"count":2,"_shards":{"total":5,"successful":5,"failed":0}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  这里指定两个type统计
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testCountMultiTypes() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index,index-child/_count?query=message:something";
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
	
		$expected = '{"count":2,"_shards":{"total":5,"successful":5,"failed":0}}';
		$this->assertEquals($expected, $result, $result);
	}	
}

