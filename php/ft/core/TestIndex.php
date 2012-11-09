<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/index_.html
 * 说明:
 *   调用es restapi建立索引的各种方式，没有对索引内容正确性进行完整断言
 * @author 	can.zhaoc
 * 
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
	 * 	在test索引index类型下建立id为1的索引，索引内容只有一个field
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回的json索引
	 */
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
	
	/**
	 * 说明：
	 * 	在test索引index类型下建立id为1的索引，索引内容多于map指定的field
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回的json索引
	 */
	public function testIndexWithIdMoreFields() {
		$method = "PUT";
		$id = 2;
		$url = "http://10.232.42.205/test/index/" . $id ;
		self::$ch = curl_init($url);
	
		$index = '{
		    "user" : "kimchy",
		    "post_date" : "2009-11-15T14:12:12",				
		    "message" : "trying out Elastic Search"
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"2","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}	
	
	
	/**
	 * 说明：
	 * 	通过version进行乐观并发锁控制，测试version自动累加
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回的json索引,version自动+1
	 */	
	public function testIndexSetVersionAutomatically() {
		$method = "PUT";
		$id = 3;
		$url = "http://10.232.42.205/test/index/" . $id;
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying Elastic Search Version"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$index = '{
		    "message" : "trying Elastic Search Version again! version should be 2"
		}';		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$result = curl_exec(self::$ch);		
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"3","_version":2}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}

	/**
	 * 说明：
	 * 	通过version进行乐观并发锁控制，当有多个版本时，可以指定最新版本更新
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回的json索引,version自动+1
	 */
	public function testIndexUpdateLastVersion() {
		$method = "PUT";
		$id = 4;
		$url = "http://10.232.42.205/test/index/" . $id;
		self::$ch = curl_init($url);
	
		$index = '{
		    "message" : "trying Elastic Search Version"
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
	
		$result = curl_exec(self::$ch);
	
		for($i = 0; $i < 2; $i++) {
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			$index = '{
			    "message" : "trying Elastic Search Version again! version should be "' . ($i+2) . '
			}';
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			$result = curl_exec(self::$ch);
		}
	
		$url = "http://10.232.42.205/test/index/" . $id . "/?version=3";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$index = '{
		    "message" : "trying Elastic Search Version again! version should be 4"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"4","_version":4}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}	
	
	/**
	 * 说明：
	 * 	通过version进行乐观并发锁控制，当对非当前版本进行更新时，会产生冲突
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	产生冲突，http 409错误
	 */
	public function testIndexVersionConflict() {
		$method = "PUT";
		$id = 5;
		$url = "http://10.232.42.205/test/index/" . $id;
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying Elastic Search Version"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$index = '{
		    "message" : "trying Elastic Search Version again! version should be 2"
		}';
		curl_exec(self::$ch);
		
		//制造冲突
		$url = "http://10.232.42.205/test/index/" . $id . "/?version=1";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$result = curl_exec(self::$ch);
		
		$expected = '{"error":"VersionConflictEngineException[[test][1] [index][5]: version conflict, current [2], provided [1]]","status":409}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}	
	
	/**
	 * 说明：
	 * 	通过op_type参数强制创建索引，行为是put-if-absent，如果id相同会失败，这里先测试成功的情况
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回json索引
	 */	
	public function testIndexOperationType() {
		$method = "PUT";
		$id = 6;
		$url = "http://10.232.42.205/test/index/" . $id . "?op_type=create" ;
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
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"6","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}
	
	/**
	 * 说明：
	 * 	通过_create强制创建索引，行为是put-if-absent，如果id相同会失败，这里测试失败的情况
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	产生冲突，http 409错误
	 */	
	public function testIndexCreationFail() {
		$method = "PUT";
		$id = 7;
		$url = "http://10.232.42.205/test/index/" . $id . "/_create" ;
		self::$ch = curl_init($url);
	
		$index = '{
		    "message" : "trying out Elastic Search"
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
	
		curl_exec(self::$ch);
		
		//制造冲突
		$result = curl_exec(self::$ch);
	
		$expected = '{"error":"DocumentAlreadyExistsException[[test][3] [index][7]: document already exists]","status":409}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}	
	
	/**
	 * 说明：
	 * 	没有指定id，则自动生成id
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回json索引，这里只简单通过正则表达式判断了id有值，并未判断id正确性(缺乏id生成的算法)
	 */	
	public function testIndexWithoutId() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index" ;
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying out Elastic Search, Without Id"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"[\w\-]{22}","_version":1}';
		$this->AssertRegExp($expected, $result, $expected . " || " . $result);		
	}
	
	/**
	 * 说明：
	 * 	routing方式建立索引
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回json索引，routing方式的建立有点疑问，参见http://elasticsearch-users.115913.n3.nabble.com/problem-using-routing-index-search-td4024816.html
	 */	
	public function testRouting() {
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
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"[\w\-]{22}","_version":1}';
		$this->AssertRegExp($expected, $result, $expected . " || " . $result);	
		$this->assertFalse(false, "没有验证routing正确性");	
	}
	
	public function testParentChild() {
		//建立parent索引
		$method = "PUT";
		$parent_id = 8;
		$url = "http://10.232.42.205/test/index/" . $parent_id ;
		self::$ch = curl_init($url);
		
		$index = '{
			"user" : "parent",
		    "message" : "some good news"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);		
		
		//建立parent-child关系
		$method = "PUT";
		$url = "http://10.232.42.205/test/index-child/_mapping" ;
		$index = '{
			"index-child" : {"_parent" : {"type" : "index"}}
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		$result = curl_exec(self::$ch);		
		
		//建立child索引
		$method = "PUT";
		$child_id = 1;
		$url = "http://10.232.42.205/test/index-child/" . $child_id . "?parent=" . $parent_id ;
		$index = '{
			"user" : "child",
			"comment" : "child comments"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		$result = curl_exec(self::$ch);		
		$expected = '{"ok":true,"_index":"test","_type":"index-child","_id":"1","_version":1}';
		//$this->assertEquals($expected, $result, $expected . " || " . $result);
		
		sleep(1);
		
		//查询child索引
		$method = "XGET";
		$url = "http://10.232.42.205/test/_search";
		$query = '{ 
		    "query" : { 
		        "has_child" : {
		            "type" : "index-child", 
		            "query" :{"term" : {"user" : "child"} }
		        }
		    }
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);	
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"8","_score":1.0, "_source" : {
			"user" : "parent",
		    "message" : "some good news"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);	
	}
}

