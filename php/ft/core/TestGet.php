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
class TestGet extends PHPUnit_Framework_TestCase {
	
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
	 * 	根据id查询索引
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回的json索引
	 */
	public function testGetById() {
		$method = "XGET";
		$url = "http://10.232.42.205/test/index/" . self::$id;

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
		
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":1,"exists":true, "_source" : {
		    "message" : "trying out Elastic Search"
		}}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * XHEAD
	 * 如果存在返回的head中包含200，否则是404
	 * curl -XGET 'http://localhost:9200/twitter/_status'可以获取更详细的信息
	 */
	public function testXHEADExist() {
		$method = "HEAD";
		$url = "http://10.232.42.205/test/index/" . self::$id;
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_HEADER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
		
		$expected = "HTTP/1.1 200 OK\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Length: 0\r\n\r\n";
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * XHEAD
	 * 如果存在返回的head中包含200，否则是404
	 * curl -XGET 'http://localhost:9200/twitter/_status'可以获取更详细的信息
	 */
	public function testXHEADNotExist() {
		$method = "HEAD";
		$url = "http://10.232.42.205/test/index/" . (self::$id+1);
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_HEADER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
	
		$expected = "HTTP/1.1 404 Not Found\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Length: 0\r\n\r\n";
		$this->assertEquals($expected, $result, $result);
	}
	
	/**
	 * GET接口默认就是实时的，并且不受refresh频率的影响（refresh之后可以被search到）
	 * 
	 * 如果要停用realtime方式的GET，可以通过设置参数 realtime 为 false ,或者在节点级别做全局的设置， action.get.realtime 
	 * 
	 * 当获取一个文档的时候，可以通过设置参数 fields 来返回只需要的字段，如果可能的话，会优先从索引里面（字段存储（store）设置为true）拿数据，当使用实时GET的时候，es会从source来抽取字段信息。
	 */
	public function testRealtime() {
		
	}
	
	/**
	 * get接口的类型为可选的，如果设置为 _all ，则会从所有的类型中去拿第一个和id匹配的文档。
	 */
	public function testOptionalType() {
		
	}
	
	/**
	 * 说明：
	 * 	根据id查询索引，并设置返回的字段，如果字段store，则从store中抽取，否则从_source中获得
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回只包含指定字段的json索引
	 */	
	public function testGetFields() {
		$method = "XGET";
		$url = "http://10.232.42.205/test/index/" . self::$id . "?fields=message";
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
		
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id  .'","_version":1,"exists":true,"fields":{"message":"trying out Elastic Search"}}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * 索引的时候可以通过routing来控制路由，那么获取该文档的时候，也需要提供对应的routing值
	 */
	public function testGetByRouting() {
		
	}
	
	/**
	 * 通过参数 preference 来配置优先到哪一个shard副本上执行，默认是随机选取
	 * _primary: 只在主碎片上执行（每个复制组，有一个主分片）
	 * _local: 如果可能只在本地分片上执行
	 * 自定义的值:(不是特别清楚作用)
	 */
	public function testPreference() {
		
	}
	
	/**
	 * 当设置刷新参数 refresh 为 true 的时候，会预先执行刷新操作来保证所有的内容都可以搜索到，同样注意该操作造成的负载
	 */
	public function testRefresh() {
		
	}
	
	/**
	 * get操作会hash到指定的shard id上去，然后跳转到该shard id对应的其中一个副本上，然后返回相应的数据，
	 * 此副本作为主分片，并和其他副本在同一个shard id group中。 也就是说，副本越多，get操作的伸缩性越好。
	 */
	public function testDistributed() {
		
	}
}

