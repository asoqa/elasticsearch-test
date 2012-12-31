<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/validate.html
 * 说明:
 *   调用es restapi验证query
 * @author 	can.zhaoc
 *
 */
class TestValidate extends PHPUnit_Framework_TestCase {
	
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
		            "postDate" : {"type" : "long", "store" : "yes"}
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
	 * 	validate验证query的合法性
	 * 前提：
	 * 	
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testValidateTrue() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_validate/query?q=postDate:123456";

		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
		
		$expected = '{"valid":true,"_shards":{"total":1,"successful":1,"failed":0}}';
		$this->assertEquals($expected, $result, $result);		
	}
	
	
	/**
	 * 说明：
	 * 	query不合法，比如这里postDate在mapping中是long型，就不能允许字符
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testValidateFalse() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_validate/query?q=postDate:abcd";
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
	
		$expected = '{"valid":false,"_shards":{"total":1,"successful":1,"failed":0}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	query不合法，这里带上request body
	 * 前提：
	 *
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testValidateFalseWithRequestBody() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_validate/query";
		
		$query = '{
		  "filtered" : {
		    "query" : {
		      "query_string" : {
		        "query" : "*:*"
		      }
		    },
		    "filter" : {
		      "term" : { "postDate" : "kimchy" }
		    }
		  }
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"valid":false,"_shards":{"total":1,"successful":1,"failed":0}}';
		$this->assertEquals($expected, $result, $result);
	}	
}

