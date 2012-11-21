<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/multi-get.html
 * 说明:
 *   调用es restapi查询索引的各种方式
 * @author 	can.zhaoc
 *
 */
class TestMultiGet extends PHPUnit_Framework_TestCase {
	
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
	 * 	_mget接口，指定一个index+type+id的数组，进行查询
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录的json索引
	 */
	public function testMultiGetById() {
		$method = "GET";
		$url = "http://10.232.42.205/_mget";
		
		$query = '{
		    "docs" : [
		        {
		            "_index" : "test",
		            "_type" : "index",
		            "_id" : "' . (self::$id-1) . '"
		        },
		        {
		            "_index" : "test",
		            "_type" : "index",
		            "_id" : "' . self::$id . '"
		        }
		    ]
		}';

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}]}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 * 	_mget接口，参数指定一个type+id的数组，但是不指定index，进行查询；index通过url中指定
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录的json索引
	 */
	public function testMultiGetWithoutIndex() {
		$method = "GET";
		$url = "http://10.232.42.205/test/_mget";
	
		$query = '{
		    "docs" : [
		        {
		            "_type" : "index",
		            "_id" : "' . (self::$id-1) . '"
		        },
		        {
		            "_type" : "index",
		            "_id" : "' . self::$id . '"
		        }
		    ]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	_mget接口，参数指定一个id的数组，但是不指定index和type，进行查询；index和type通过url中指定
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录的json索引
	 */
	public function testMultiGetWithoutTypeAndIndex() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_mget";
	
		$query = '{
		    "docs" : [
		        {
		            "_id" : "' . (self::$id-1) . '"
		        },
		        {
		            "_id" : "' . self::$id . '"
		        }
		    ]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	_mget接口，简化指定一个id的数组，通过ids配置
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录的json索引
	 */
	public function testMultiGetByOnlyIds() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_mget";
	
		$query = '{
 			"ids" : ["' . (self::$id-1) . '", "' . (self::$id) . '"]				
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	_查找结果中有不存在的记录
	 * 前提：
	 * 	准备两条索引，查3条
	 * 判断：
	 * 	1.	返回包含两条成功记录和一条失败记录的json索引
	 */	
	public function testMultiGetPartial() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_mget";
	
		//$id+1不存在
		$query = '{
 			"ids" : ["' . (self::$id-1) . '", "' . (self::$id) . '", "' . (self::$id+1) . '"]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 1"
		}},{"_index":"test","_type":"index","_id":"' . (self::$id) . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search 2"
		}},{"_index":"test","_type":"index","_id":"' . (self::$id+1) . '","exists":false}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	_指定返回的字段
	 * 前提：
	 * 	准备两条索引
	 * 判断：
	 * 	1.	返回包含两条记录指定字段的json索引
	 */
	public function testMultiGetByFields() {
		$method = "GET";
		$url = "http://10.232.42.205/_mget";
	
		$query =  '{
		    "docs" : [
		        {
		            "_index" : "test",
		            "_type" : "index",
		            "_id" : "' . (self::$id-1) . '",
		            "fields" : ["message"]
		        },
		        {
		            "_index" : "test",
		            "_type" : "index",
		            "_id" : "' . (self::$id) . '",
		            "fields" : ["message"]
		        }
		    ]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"docs":[{"_index":"test","_type":"index","_id":"' . (self::$id-1) . '","_version":1,"exists":true,"fields":{"message":"trying out Elastic Search 1"}},{"_index":"test","_type":"index","_id":"' . (self::$id) . '","_version":1,"exists":true,"fields":{"message":"trying out Elastic Search 2"}}]}';
		$this->assertEquals($expected, $result, $result);
	}	
}

