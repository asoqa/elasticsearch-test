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
		$url = "http://10.232.42.205/test";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
				
		$url = "http://10.232.42.205/test1";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$result = curl_exec(self::$ch);
		
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
		
		$url = "http://10.232.42.205/test1";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
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
	
	/**
	 * 说明：
	 * 	建立parent-child关联索引，据说parent-child比较占内存
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回json索引，这里通过query对结果正确性进行了完整断言
	 */	
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
		
		//不然下面的查询会拿不到结果
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
	
	/**
	 * 说明：
	 * 	给索引打时间戳
	 * 前提：
	 * 	建立好map，如setUpBeforeClass所示
	 * 判断：
	 * 	1.	返回json索引，时间戳如何使用还不清楚
	 */	
	public function testTimestamp() {
		$method = "PUT";
		$id = 9;
		$url = "http://10.232.42.205/test/index/" . $id . "?timestamp=2012-11-11T14%3A12%3A12";
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying timestamp"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"'. $id . '","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}
	
	/**
	 * 说明：
	 * 	ttl到期先打上删除标记，等indices.ttl.interval到之后合并做真正的删除
	 * 前提：
	 * 	1. 建立好map，启用ttl和timestamp配置，如setUpBeforeClass所示
	 *  2. 设置集群indices.ttl.interval=1，这样能够迅速确认ttl失效
	 * 判断：
	 * 	1.	返回json索引，indices.ttl.interval到之后应该查询不到
	 */	
	public function testTTL() {
		$method = "PUT";
		$id = 10;
		$url = "http://10.232.42.205/test/index/" . $id . "?ttl=5000";
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying ttl"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"'. $id . '","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);	
		
		//过ttl时间后校验
		sleep(7);
		$method = "XGET";
		$url = "http://10.232.42.205/test/index/" . $id;
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);		
		$expected = '{"_index":"test","_type":"index","_id":"'. $id . '","exists":false}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);
	}
	
	/**
	 * percolate功能可以过滤特定查询条件
	 * 比如先定义一个查询key为kuku，实际内容查询field1=value1的查询条件
	 * 这样当有符合field1=value1的query产生时，都会匹配到kuku这条记录
	 */
	public function testPercolate() {
		//这个用例放在percolate模块单独测试
	}
	
	/**
	 * 索引先直接写主分片，这个操作发生在包含此分片的实际节点上。主分片完成操作后，根据需要，将更新分布到可用备份中。
	 */
	public function testDistribute() {
		
	}
	
	/**
	 *  为了防止写操作发生在坏的分区上。默认情况下，索引操作仅在活动分片数量>标准（副本数量/2+1）的情况下进行。
	 *  通过 action.write_consistency配置可以定制这个默认行为。可用的值有one,quorum,all
	 */
	public function testWriteConsistency() {
		
	}
	
	/**
	 * 异步备份
	 * 默认情况下，索引操作在所有分片（包括备份中的）都完成之后才返回（同步备份）。为了启用异步备份，使得备份过程
	 * 在后台进行，可以将replication参数设为async。当异步备份使用时，索引操作在主分片完成后立即返回。
	 */
	public function testAsynchronousReplication() {
		
	}
	
	/**
	 * 通过设置refresh参数为true，可以使得索引立刻更新，这样在搜索结果中能够立刻体现。
	 * 需要谨慎使用这个功能，因为可能会导致性能的降低，包括索引和搜索过程，这个过程是完全实时的。
	 * 
	 */
	public function testRefresh() {
		
	}
	
	/**
	 * 说明：
	 * 	索引在写入时，主分片不一定处于可用状态。比如主分片正在从gateway或备份中恢复。
	 * 	默认情况下索引写入时会有个1分钟的超时时间，等待主分片可用。超时时间可以通过timeout参数配置。
	 * 前提：
	 * 判断：
	 * 	1.	返回json索引，5分钟超时时间（这里不方便验证，掠过）
	 */
	public function testTimeout() {
		$method = "PUT";
		$id = 11;
		$url = "http://10.232.42.205/test/index/" . $id . "?timeout=5m";
		self::$ch = curl_init($url);
		
		$index = '{
		    "message" : "trying timeout"
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"'. $id . '","_version":1}';
		$this->assertEquals($expected, $result, $expected . " || " . $result);		
	}
	
	/**
	 * 说明：
	 * 	添加索引别名。添加后通过指定别名进行索引、查询等操作，和在实际的index是完全等效的。
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */	
	public function testAddAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
		
		$query = '{ 
		    "actions" : [ 
		        { "add" : { "index" : "test", "alias" : "alias1" } } 
		    ] 
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 * 	删除索引别名
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testRemoveAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{
		    "actions" : [
		        { "add" : { "index" : "test", "alias" : "alias1" } }
		    ]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	修改索引别名，是通过先删除再添加的办法实现的
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testUpdateAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{ 
		    "actions" : [ 
		        { "remove" : { "index" : "test", "alias" : "alias1" } }, 
		        { "add" : { "index" : "test", "alias" : "alias2" } } 
		    ] 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	多个索引库共用一个别名
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testAliasMultiIndices() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{
		    "actions" : [
		        { "add" : { "index" : "test", "alias" : "alias1" } },
		        { "add" : { "index" : "test1", "alias" : "alias2" } }
		    ]
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	可以给别名加filter。这样的用处是，可以给同一个索引建立不同的视图。filter用Query DSL定义，可以用于
	 * 查询、计数、删除、More Like This等等。
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testFilteredAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{ 
		    "actions" : [ 
		        { 
		            "add" : { 
		                 "index" : "test", 
		                 "alias" : "alias2", 
		                 "filter" : { "term" : { "user" : "kimchy" } } 
		            } 
		        } 
		    ] 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	可以在别名基础上建立路由。这样的用处是可以和filter alias结合，避免不必要的shard操作。
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testRoutingAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{ 
		    "actions" : [ 
		        { 
		            "add" : { 
		                 "index" : "test", 
		                 "alias" : "alias1", 
		                 "routing" : "1" 
		            } 
		        } 
		    ] 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	可以针对搜索和索引两种操作分别建立路由。其中搜索路由可以包含多个值，因为可能涉及到在多个路由（shard）上进行查询；
	 *  索引路由只能是单值。
	 *  如果有路由别名，但同时也通过routing参数指定了路由（如下），则两者的交集作为路由。比如下面这个查询，将使用2作为路由值.
	 *  curl -XGET 'http://localhost:9200/alias2/_search?q=user:kimchy&routing=2,3'
	 * 前提：
	 *  索引库必须存在
	 * 判断：
	 * 	1.	操作成功
	 */
	public function testDifferentRoutingAlias() {
		$method = "POST";
		$id = 11;
		$url = "http://10.232.42.205/_aliases";
		self::$ch = curl_init($url);
	
		$query = '{ 
		    "actions" : [ 
		        { 
		            "add" : { 
		                 "index" : "test", 
		                 "alias" : "alias2", 
		                 "search_routing" : "1,2", 
		                 "index_routing" : "2" 
		            } 
		        } 
		    ] 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
	
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 通过下面的语句可以查询当前别名
	 * curl -XGET 'localhost:9200/test/_aliases' 
		curl -XGET 'localhost:9200/test1,test2/_aliases' 
		curl -XGET 'localhost:9200/_aliases'
	 */
	public function testGetAliases() {
		
	}
	
	/**
	 通过yaml的方式创建索引
	 $ curl -XPUT 'http://localhost:9200/twitter/' -d '
		index :
		    number_of_shards : 3
		    number_of_replicas : 2
		'
	 */
	public function testCreateIndexByYaml() {
		
	}
	
	/**
	 * 可以open和close索引库。索引库被关闭后不会在集群上产生开销（除了metadata的维护），不能进行
	 * 读写操作。
	 */
	public function testCloseAndOpenIndex() {
		$method = "POST";
		$url = "http://10.232.42.205:9200/test/_close";
		self::$ch = curl_init($url);
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
	
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
		
		$url = "http://10.232.42.205:9200/test/_open";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		$result = curl_exec(self::$ch);
		
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 获取setting信息
	 * $ curl -XGET 'http://localhost:9200/twitter/_settings'
	 */
	public function testGetSettings() {
		
	}
	
	/**
	 * $ curl -XGET 'http://localhost:9200/twitter/tweet/_mapping'
	 * 可以指定多个索引库，以逗号分隔
	 */
	public function testGetMapping() {
		
	}
	
	/**
	 * optimize通过合并segments从而减少segment数量，来达到优化索引、提高查询速度的目的
	 * $ curl -XPOST 'http://localhost:9200/twitter/_optimize'
	 */
	public function testOptimizeIndices() {
		
	}
	
	/**
	 * index的flush过程将内存数据写入存储
	 * $ curl -XPOST 'http://localhost:9200/twitter/_flush'
	 */
	public function testFlushIndices() {
		
	}
}

