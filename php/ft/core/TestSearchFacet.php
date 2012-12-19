<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/search/facets/
 * 说明:
 *   facet的使用。
 *   term facet会加载相关字段内容进内存。这意味着每个分片，应该有足够的内存。建议将
 *   字段设为not_analyzed或者保证字段唯一性令牌的数量不会太大。
 * @author 	can.zhaoc
 *
 */
class TestSearchFacet extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "title" : {"type" : "string", "store" : "yes"},
                    "tags" : {"type" : "string", "store" : "no"},
                    "postDate" : {"type" : "date", "store" : "no"},
				    "age" : {"type" : "long", "store" : "no"},
            		"obj" : {"type" : "nested"},
		        }
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
		for($i=1; $i<=3; $i++) {
			$url = "http://10.232.42.205/test/index/" . $i;
						
			$index = file_get_contents(__DIR__ . "\\data\\facet" . $i .".json");
			
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
		$url = "http://10.232.42.205/test/index";
		
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
	 	facet filter区别于后面的filter facet。是通过配置filter，减少需要聚合的文档数量。
	 *
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testFacetFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "facets" : { 
		        "tag_facet" : { 
				            "terms" : { 
				                "field" : "tags"
				            } , 
		        "facet_filter" : { 
					"range" : {
						"age" : { 
							"from" : "1", 
							"to" : "10", 
							"include_lower" : true, 
							"include_upper" : false
						}
					}
				} 
				} 
			} 
		} ';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tag_facet":{"_type":"terms","missing":0,"total":3,"other":0,"terms":[{"term":"foo","count":2},{"term":"bar","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}
		
	/**
	 * 说明：
          facet聚合根据指定字段返回最常用的n个term聚合信息。这里通过size指定n=2
	 *  
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testTermsFacet() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{ 
		    "query" : { 
		        "match_all" : {  } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "size" : 2 
		            } 
		        } 
		    } 
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":3,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
	"title" : "one" ,					
	"tags" : \["foo"\],
	"postDate" : "2009-11-15T14:12:12",						
	"age" : 1,
    "obj" : { 
	    "name" : "green", 
	    "count" : 3
	}
}},{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
	"title" : "two" ,					
	"tags" : \["foo", "bar"\],
	"postDate" : "2009-11-15T14:12:13",						
	"age" : 3,
    "obj" : { 
	    "name" : "blue", 
	    "count" : 4
	}
}},{"_index":"test","_type":"index","_id":"3","_score":1.0, "_source" : {
	"title" : "three" ,					
	"tags" : \["foo", "bar", "baz"\],
	"postDate" : "2009-11-15T14:12:14",						
	"age" : 10,
    "obj" : { 
	    "name" : "black", 
	    "count" : 9
	}
}}]},"facets":{"tags":{"_type":"terms","missing":0,"total":6,"other":2,"terms":\[{"term":"foo","count":3},{"term":"baz","count":1}\]}}}';
		$this->AssertRegExp($expected, $result, $result);	
	}

	/**
	 * 说明：
	 指定facet中term的排序，支持count|term|reverse_count|reverse_term，默认count，这里为reverse_count，即按count升序排序
	 *
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testTermsFacetOrder() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "query_string" : {"query" : "T*" } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "size" : 10, 
		                "order" : "reverse_count" 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":5,"other":0,"terms":[{"term":"baz","count":1},{"term":"bar","count":2},{"term":"foo","count":2}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 	获取term facet的所有的term，哪怕是没有在搜索结果中的，没匹配上的count=0.这里例子中baz的count=0
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testAllTerms() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "query_string" : { "query": "One OR Two" } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "all_terms" : true 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":3,"other":0,"terms":[{"term":"foo","count":2},{"term":"bar","count":1},{"term":"baz","count":0}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	   指定从terms facet中需要排除的terms，这里排除foo和bar，只保留baz的聚合
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testExcludingTerms() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : { } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "exclude" : ["foo", "bar"] 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":6,"other":5,"terms":[{"term":"baz","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	    可以通过正则表达式过滤terms，其中regex_flags的参数参见java pattern api
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testRegexPatterns() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : { } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "regex" : "ba.*", 
		                "regex_flags" : "DOTALL" 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":6,"other":3,"terms":[{"term":"bar","count":2},{"term":"baz","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	   允许定义terms facet的script进行处理。
	   处理脚本可以通过string类型替换term
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testTermScriptsWithString() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {  } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "size" : 10, 
		                "script" : "term + \'aaa\'" 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":6,"other":0,"terms":[{"term":"fooaaa","count":3},{"term":"baraaa","count":2},{"term":"bazaaa","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 允许定义terms facet的script进行处理。
	 处理脚本可以返回一个bool值，true表示include，false表示exclude
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testTermScriptsWithBoolean() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {  } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "field" : "tags", 
		                "size" : 10, 
		                "script" : "term == \'foo\' ? true : false" 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":3,"other":0,"terms":[{"term":"foo","count":3}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 允许处理多个字段的聚合
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testMultiFields() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {  } 
		    }, 
		    "facets" : { 
		        "tags" : { 
		            "terms" : { 
		                "fields" : ["tags", "title"], 
		                "size" : 10
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"tags":{"_type":"terms","missing":0,"total":9,"other":0,"terms":[{"term":"foo","count":3},{"term":"bar","count":2},{"term":"two","count":1},{"term":"three","count":1},{"term":"one","count":1},{"term":"baz","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	   对字段本身也支持script
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testFieldScript() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {  } 
		    }, 
		    "facets" : { 
		        "my_facet" : { 
		            "terms" : { 
		                "script_field" : "_source.tags", 
		                "size" : 10 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"my_facet":{"_type":"terms","missing":0,"total":6,"other":0,"terms":[{"term":"foo","count":3},{"term":"bar","count":2},{"term":"baz","count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 对字段本身也支持script
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testRangeFacets() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "range1" : { 
		            "range" : { 
		                "field" : "age", 
		                "ranges" : [ 
		                    { "from" : 1, "to" : 5 },
							{ "from" : 6, "to" : 10 },
							{ "from" :11 }				
		                ] 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"range1":{"_type":"range","ranges":[{"from":1,"to":5,"count":2,"min":1,"max":3,"total_count":2,"total":4,"mean":2},{"from":6,"to":10,"count":0,"total_count":0,"total":0,"mean":0},{"from":11,"count":0,"total_count":0,"total":0,"mean":0}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	     range facet允许多个字段的组合，结合了term设stats facet的功能。其中key_field作为聚合的主键，range后的结果按key_field进行聚合；value_field
	            是range实际进行过滤的字段。
	            注意key_field必须是数值或日期类型，这里以obj.count聚合，按照age进行range
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testRangeKeyValue() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "range1" : { 
		            "range" : { 
		                "key_field" : "obj.count", 
		                "value_field" : "age", 
		                "ranges" : [ 
							{ "from" : 1, "to" : 5 },
							{ "from" : 6, "to" : 10 },
							{ "from" :11 }	
		                ] 
		            } 
		        } 
		    } 
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"range1":{"_type":"range","ranges":[{"from":1,"to":5,"count":2,"min":1,"max":3,"total_count":2,"total":4,"mean":2},{"from":6,"to":10,"count":1,"min":10,"max":10,"total_count":1,"total":10,"mean":10},{"from":11,"count":0,"total_count":0,"total":0,"mean":0}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 可以对key/value方式的多值range进行脚本化，如下
	 * { 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "range1" : { 
		            "range" : { 
		                "key_script" : "doc['date'].date.minuteOfHour", 
		                "value_script" : "doc['num1'].value", 
		                "ranges" : [ 
		                    { "to" : 50 }, 
		                    { "from" : 20, "to" : 70 }, 
		                    { "from" : 70, "to" : 120 }, 
		                    { "from" : 150 } 
		                ] 
		            } 
		        } 
		    } 
		}
	 */
	public function testRangeScriptedKeyValue() {
		
	}
	
	/**
	 * 说明：
	        柱状图facet需要基于数值类型，按照给定的一个间隔建立柱状图。每个值都会落入一个区间内。
	   这里按age间隔2进行facet，age=1落入0-1的区间，age=3落入2-3的区间，age=10落入10-11的区间。
	   比较耗内存，默认对将数值类型当作long或double处理，如果可能最好事先指定类型为short,float等以节省内存
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testHistogramFacets() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "histo1" : { 
		            "histogram" : { 
		                "field" : "age", 
		                "interval" : 2 
		            } 
		        } 
		    } 
		}    ';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"histo1":{"_type":"histogram","entries":[{"key":0,"count":1},{"key":2,"count":1},{"key":10,"count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	   facet也可以基于日期类型，通过time_interval指定区间范围。这里按2秒聚类。
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testHistogramDateFacets() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "histo1" : { 
		            "histogram" : { 
		                "field" : "postDate", 
		                "time_interval" : "2s"
		            } 
		        } 
		    } 
		}    ';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"histo1":{"_type":"histogram","entries":[{"key":1258294332000,"count":2},{"key":1258294334000,"count":1}]}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * histogram facet也和上面一样支持key/value形式的多字段聚合。其中key_field指定聚合区间依赖的字段，value_field指定区间内容统计依赖的字段，会统计对应字段的最大值、最小值等。
	 * interval针对key_field;
	 * 同样也支持script脚本化
	 */
	public function testHistogramKeyValueFacets() {
		
	}
	
	/**
	 * 日期类型的histogram facet，也可以通过专门的date_histogram来事先，比如：
	 { 
	    "query" : { 
	        "match_all" : {} 
	    }, 
	    "facets" : { 
	        "histo1" : { 
	            "date_histogram" : { 
	                "field" : "field_name", 
	                "interval" : "day" 
	            } 
	        } 
	    } 
	}
	  interval允许为year, quarter, month, week, day, hour, minute
	      可以通过time_zone指定时区，其他同上
	 */
	public function testDateHistogramFacets() {
		
	}
	
	/**
	 * 说明：
	 	fiter facet区别于facet filter。filter facet只简单的统计filter后的命中数量。其语法参考query dsl。
	 	因此filter facet是非常快的，比任何基于query的facet都快。
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testFilterFacet() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "facets" : { 
		        "age_facet" : { 
		            "filter" : { 
					            "range" : {
					                "age" : { 
					                    "from" : "1", 
					                    "to" : "10", 
					                    "include_lower" : true, 
					                    "include_upper" : false
					                }
					            }
		            } 
		        } 
		    } 
		} ';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$result = json_encode(json_decode($result)->facets);
		$expected = '{"age_facet":{"_type":"filter","count":2}}';
		$this->assertEquals($expected, $result, $result);
	}	
	
	/**
	 * 语法和filter facet基本相同，唯一的不同就是把filter改成query。统计的是query后的hit doc数量。
	 */
	public function testQueryFacet() {
		
	}
	
	/**
	 * 说明：
	 	对数值类型的字段进行统计，统计数据包括（计数，总和， 平方和， 平均值， 最小值，最大值，方差， 标准差）
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引，这里改变了断言的方式，只断言facet部分json字符串
	 */
	public function testStatisticalFacets() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "facets" : { 
		        "stat1" : { 
		            "statistical" : { 
		                "field" : "age" 
		            } 
		        } 
		    } 
		}    ';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		//$facet_pos = strpos($result, '"facets":{');
		//$acutal_result = substr($result, $facet_pos);
		$result = json_encode(json_decode($result)->facets);
		$expected = '"facets":{"stat1":{"_type":"statistical","count":3,"total":14.0,"min":1.0,"max":10.0,"mean":4.666666666666667,"sum_of_squares":110.0,"variance":14.888888888888891,"std_deviation":3.8586123009300755}}}';
		$this->assertEquals($expected, $acutal_result, $result);
	}	
}


