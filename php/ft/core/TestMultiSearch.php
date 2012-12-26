<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/multi-search.html
 * 说明:
 *   调用es restapi查询索引的各种方式
 * @author 	can.zhaoc
 *
 */
class TestMultiSearch extends PHPUnit_Framework_TestCase {
	
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
		$url = "http://10.232.42.205/test/index/" . (self::$id++);
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying out Elastic Search 1"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);		
		
		$url = "http://10.232.42.205/test/index/" . self::$id;		
		$index = '{
		    "message" : "trying out Elastic Search 2"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
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
		
		//$result = curl_exec(self::$ch);		
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
	 * 	_批量查询的接口。其中request body指定并发查询的内容，格式需要遵循：
	 	header\n
	 	body\n
	 	header\n
	 	body\n
	 	如此形式的格式，用\n分隔。header部分指定index,type,mapping的类型,search_type, perference,routing；body部分指定查询语句。
	 	其中：
	 	1.	如果header为空，则用{}表示
	 	2.  一个header和body的组合对应一条response记录，每个response包括对应查询的结果
	 *	
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录的json索引
	 */
	public function testMultiSearch() {
		$method = "GET";
		$url = "http://10.232.42.205/test/_msearch";
		
		$query = '{"index" : "test"} 
			{"query" : {"match_all" : {}}, "from" : 0, "size" : 10} 
			{"index" : "test", "search_type" : "count"}                              
			{"query" : {"match_all" : {}}} 
			{}                             
			{"query" : {"match_all" : {}}} 
			{"search_type" : "count"} 
			{"query" : {"match_all" : {}}} 
		';

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"responses":\[{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}]}},{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":0.0,"hits":\[\]}},{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}\]}},{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":0.0,"hits":\[\]}}\]}';
		$this->AssertRegExp($expected, $result, $result);		
	}
}

