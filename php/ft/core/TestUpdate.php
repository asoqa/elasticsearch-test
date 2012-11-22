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
	 * 	更新索引，计数器+1
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回更新成功json字符串
	 *  2.  更新内容正确
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
	
	/**
	 * 说明：
	 * 	更新索引，在tags列表中加入blue值
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回更新成功json字符串
	 *  2.  更新内容正确
	 */
	public function testAddListItem() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/" . self::$id . "/_update";
		
		$query = '{
		    "script" : "ctx._source.tags += tag",
		    "params" : {
		        "tag" : "blue"
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
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":2,"exists":true, "_source" : {"counter":1,"tags":["red","blue"]}}';
		$this->assertEquals($expected, $result, $result);		
	}

	/**
	 * 说明：
	 * 	更新索引，在document中添加新的text字段
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回更新成功json字符串
	 *  2.  更新内容正确
	 */
	public function testAddANewField() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/" . self::$id . "/_update";
	
		$query = '{
		    "script" : "ctx._source.text = \"some text\""
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
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":2,"exists":true, "_source" : {"counter":1,"tags":["red"],"text":"some text"}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	更新索引，在document中删除text字段
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回更新成功json字符串
	 *  2.  更新内容正确
	 */
	public function testRemoveAField() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/" . self::$id . "/_update";
	
		//准备测试数据
		$query = '{
		    "script" : "ctx._source.text = \"some text\""
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		//删除字段
		$query = '{
		    "script" : "ctx._source.remove(\"text\")"
		}';	
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);		
		$result = curl_exec(self::$ch);
		$expected = '{"ok":true,"_index":"test","_type":"index","_id":"' . self::$id . '","_version":3}';
		$this->assertEquals($expected, $result, $result);
	
		//断言更新内容是否正确
		$method = "GET";
		$url = "http://10.232.42.205/test/index/" . self::$id;
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","_version":3,"exists":true, "_source" : {"counter":1,"tags":["red"]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	更新索引，在document中根据特定的筛选条件删除doc
	 * 前提：
	 * 	存在该id的索引
	 * 判断：
	 * 	1.	返回更新成功json字符串
	 *  2.  doc被删除，没有匹配记录
	 */
	public function testDeleteDocByFilter() {
		$method = "POST";
		$url = "http://10.232.42.205/test/index/" . self::$id . "/_update";
	
		$query = '{
		    "script" : "ctx._source.tags.contains(tag) ? ctx.op = \"delete\" : ctx.op = \"none\"",
		    "params" : {
		        "tag" : "red"
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
		$expected = '{"_index":"test","_type":"index","_id":"' . self::$id . '","exists":false}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 0.20版本开始支持，通过部分document更新（合并），比如根据键值对替换doc
	 */
	public function testPartialUpdate() {
		
	}
	
	/**
	 * 0.20版本开始支持upsert操作，即被更新的索引不存在时，建立一条新索引
	 */
	public function testUpsert() {
		
	}
}

