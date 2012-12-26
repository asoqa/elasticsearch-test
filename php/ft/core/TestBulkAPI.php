<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/bulk.html
 * 说明:
 *   调用es restapi查询索引的各种方式
 * @author 	can.zhaoc
 *
 */
class TestBulkAPI extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	public static $id = 1;
	
	public static function setUpBeforeClass() {
		//建索引库
		$method = "PUT";
		$url = "http://10.232.42.205/test";
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
	   bulk API可以在一次调用中执行多个index/delete操作，能显著提高索引速度。rest api接口为/_bulk，需要构造如下形式的query：
	   		action_and_meta_data\n
			optional_source\n
	   其中最后必须以\n结尾, curl命令必须用--data-binary，而不能用-d，因为-d不支持换行符\n。bulk api可以指定索引库index和type类型，也可以不指定。
	   bulk操作的response是包含每个操作执行结果的大json数据结构。单个操作的失败会被跳过，不影响其他操作。没有明确的限制一次调用需要执行多少操作，需要
	   根据实际的负载来定一个合理的大小。 
	      这个case测试多个index的情况
	 * 前提：
	 * 	
	 * 判断：
	 * 	1.	返回的json索引
	 */
	public function testBulkIndex() {
		$method = "POST";
		$url = "http://10.232.42.205/_bulk";
		
		$data = '{ "index" : { "_index" : "test", "_type" : "index", "_id" : "1" } }
		{ "user" : "bulkuser1" }
		{ "index" : { "_index" : "test", "_type" : "index", "_id" : "2" } }
		{ "user" : "bulkuser2" }
		';

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"items":\[{"index":{"_index":"test","_type":"index","_id":"1","_version":1,"ok":true}},{"index":{"_index":"test","_type":"index","_id":"2","_version":1,"ok":true}}\]}';
		$this->assertRegExp($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 	这个case测试index和delete的组合情况
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的json索引
	 */	
	public function testBulkIndexAndDelete() {
		$method = "POST";
		$url = "http://10.232.42.205/_bulk";
	
		$data = '{ "index" : { "_index" : "test", "_type" : "index", "_id" : "3" } }
		{ "user" : "bulkuser1" }
		{ "delete" : { "_index" : "test", "_type" : "index", "_id" : "3" } }
		';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"items":\[{"index":{"_index":"test","_type":"index","_id":"3","_version":1,"ok":true}},{"delete":{"_index":"test","_type":"index","_id":"3","_version":2,"ok":true}}\]}';
		$this->assertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 这个case测试在给定的index和type上进行bulk操作
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的json索引
	 */
	public function testBulkWithIndexAndType() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/_bulk";
	
		$data = '{ "index" : { "_id" : "4" } }
		{ "user" : "bulkuser4" }
		{ "delete" : { "_id" : "4" } }
		';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"items":\[{"index":{"_index":"test","_type":"index","_id":"4","_version":1,"ok":true}},{"delete":{"_index":"test","_type":"index","_id":"4","_version":2,"ok":true}}\]}';
		$this->assertRegExp($expected, $result, $result);
	}

	/**
	 * BULK UDP服务延迟低，允许更简单的索引数据，默认Bulk UDP服务是禁用的，通过在config/elasticsearch.yml中设置
	 * bulk.udp.enabled:true来启用此功能
	 * bulk.udp.bulk_actions
	 * bulk.udp.bulk_size
	 * bulk.udp.flush_interval
	 * bulk.udp.concurrent_requests
	 * bulk.udp.host
	 * bulk.udp.port
	 * bulk.udp.receive_buffer_size:
	 * 
	 * 用nc命令来做个例子
	 * cat requests | nc -w 0 -u localhost 9700
	 */
	public function testBulkUDP() {
		
	}
}

