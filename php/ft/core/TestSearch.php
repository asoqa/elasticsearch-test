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
class TestSearch extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"}
                    "user" : {"type" : "string", "store" : "no"},
                    "postDate" : {"type" : "date", "store" : "no"}
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
		
		//批量准备测试数据
		$method = "PUT";
		for($i=1; $i<=10; $i++) {
			$url = "http://10.232.42.205/test/index/" . $i;
						
			$index = '{
				"user" : "kimchy' . $i . '",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search' . $i . '" 
			}';
			
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
			
			$result = curl_exec(self::$ch);		
		}	
		
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
	 * http://www.elasticsearch.org/guide/reference/api/search/request-body.html
	 * 说明：
	 * 	构造QSL进行搜索，加上了timeout参数以确保能搜到
	 *  允许的参数有：
	 *  timeout:
	 *  from:
	 *  size:
	 *  search_type:
	 *  
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchQuestBody() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
		    "query" : {
		        "term" : { "user" : "kimchy1" }
		    }
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);	
	}
	
	/**
	 * http://www.elasticsearch.org/guide/reference/api/search/uri-request.html
	 * 说明：
	 * 	构造uri requst进行搜索
	 *  允许的参数有：
	 *  q:
	 *  df:
	 *  analyzer:
	 *  default_operator:
	 *  explain:
	 *  fields:
	 *  sort:
	 *  track_scores:
	 *  timeout:
	 *  from:
	 *  size:
	 *  search_type:
	 *  lowercase_expanded_terms:
	 *  analyze_wildcard:
	 *  
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchByURI() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search?q=user:kimchy1";
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * http://www.elasticsearch.org/guide/reference/query-dsl/
	 * 说明：
	 *  以下case针对的是在Request body中支持的query dsl语法
	 * 	包括基本查询类型、组合查询类型、过滤器等待
	 *  
	 */
	
	
	
	/**
	 * 说明：
	 * 	Match查询支持text/numerics/dates类型,可以指定 operator为and或者or
	 *  不是特别理解match查询的作用
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1. 
	 */	
	public function testMatchOperator() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
		    "query" : {
				"match" : { 
				        "message" : "trying out Elastic Search1",
						"operator" : "and"
				    } 
		    }
		}';		
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = file_get_contents(__DIR__ . "\\TestSearch_testMatchQuery.json");
		$this->AssertRegExp($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 * 	Match查询支持text/numerics/dates类型
	 *  不是特别理解match查询的作用
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.
	 */
	public function testMatchPhrase() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "query" : {
				"match" : {
				        "message" : "trying out Elastic Search1",
						"operator" : "and"
				    }
		    }
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = file_get_contents(__DIR__ . "\\TestSearch_testMatchQuery.json");
		$this->AssertRegExp($expected, $result, $result);
	}	
}

