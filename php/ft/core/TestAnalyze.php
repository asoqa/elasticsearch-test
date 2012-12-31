<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/admin-indices-analyze.html
 * 说明:
 *   调用es restapi验证query
 * @author 	can.zhaoc
 *
 */
class TestAnalyze extends PHPUnit_Framework_TestCase {
	
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
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated TestIndex::tearDown()
		parent::tearDown ();
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
	 *  执行分词，看看对文本分词后返回的token信息。这里测试自带的standard analyzer。
	 * 前提：
	 * 	
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testStandardAnalyzer() {
		$method = "GET";
		$url = "http://10.232.42.205/_analyze?analyzer=standard";

		$query = 'this is a test';
				
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"tokens":[{"token":"test","start_offset":10,"end_offset":14,"type":"<ALPHANUM>","position":4}]}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 *  可以在分词器中定制内部的tokenize和filter。
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testCustomAnalyzer() {
		$method = "GET";
		$url = "http://10.232.42.205/_analyze?tokenizer=keyword&filters=lowercase";
	
		$query = 'this is a test';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"tokens":[{"token":"this is a test","start_offset":0,"end_offset":14,"type":"word","position":1}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  可以在指定的index上执行，这时候用的是index指定的分词器。
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testAnalyzeOnIndex() {
		$method = "GET";
		$url = "http://10.232.42.205/test/_analyze?text=this+is+a+test";
	
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
	
		$expected = '{"tokens":[{"token":"test","start_offset":10,"end_offset":14,"type":"<ALPHANUM>","position":4}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  可以在指定的index上执行，但是指定不同的分词器。
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testCustomAnalyzerOnIndex() {
		$method = "GET";
		$url = "http://10.232.42.205:9200/test/_analyze?analyzer=whitespace";
	
		$query = 'this is a test';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"tokens":[{"token":"this","start_offset":0,"end_offset":4,"type":"word","position":1},{"token":"is","start_offset":5,"end_offset":7,"type":"word","position":2},{"token":"a","start_offset":8,"end_offset":9,"type":"word","position":3},{"token":"test","start_offset":10,"end_offset":14,"type":"word","position":4}]}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  可以在指定的 字段级别上执行，这时候默认用字段对应的分词器。注意被分词的内容需要与字段类型对应。
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testAnalyzeOnField() {
		$method = "GET";
		$url = "http://10.232.42.205:9200/test/_analyze?field=message";
	
		$query = 'this is a test';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"tokens":[{"token":"test","start_offset":10,"end_offset":14,"type":"<ALPHANUM>","position":4}]}';
		$this->assertEquals($expected, $result, $result);
	}	

	/**
	 * 说明：
	 *  可以在指定的 字段级别上执行，这时候默认用字段对应的分词器。注意被分词的内容需要与字段类型对应。
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testCustomAnalyzerOnField() {
		$method = "GET";
		$url = "http://10.232.42.205:9200/test/_analyze?field=message&analyzer=keyword";
	
		$query = 'this is a test';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"tokens":[{"token":"this is a test","start_offset":0,"end_offset":14,"type":"word","position":1}]}';
		$this->assertEquals($expected, $result, $result);
	}	
}

