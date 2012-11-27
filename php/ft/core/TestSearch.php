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
		
		//$result = curl_exec(self::$ch);		
		
		$url = "http://10.232.42.205/test/index-child";
		curl_setopt(self::$ch, CURLOPT_URL, $url);		
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
	 *  match一套查询不走"query parsing"流程，因此不支持field name前缀匹配、wildcard通配符以及其他高级特性；
	 *  所以match查询失败的几率非常小（几乎不存在），并且与真实的查询行为（搜索框的查询）非常一致
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
	 * 	match支持phrase词组查询，默认slop=0（slop为词组匹配时的移动次数），也就是精确匹配
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testMatchPhrase() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "query" : {
			    "match_phrase" : {
			        "message" : "Elastic Search1"
			    }
		    }
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.79726744,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.79726744, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	match支持phrase词组前缀，基本和phrase查询一样，但是可以允许最后一个term按前缀匹配
	 * 通过 "max_expansions" : 10类似的可以控制匹配前缀的长度，提升匹配效率
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testMatchPhrasePrefix() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "query" : {
			    "match_phrase_prefix" : {
			        "message" : "trying out Elastic Search2"
			    }
		    }
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.3918023,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":1.3918023, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	multi_match支持多字段查询，可以是bool或dis_max查询。这里在user和message查询含有kimchy1的索引
	 * 除了match支持的参数外，还支持三个参数：
	 * fields:查询字段
	 * use_dis_max:boolean值，是dis_max查询还是bool查询。默认为true，即dis_max查询
	 * tie_breaker:不懂...，貌似是平衡不同字段之间分值的设置
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */	
	public function testMultiMatch() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
		    "query" : {
			  "multi_match" : {
			    "query" : "kimchy1",
			    "fields" : [ "user", "message" ]
			  }
			}
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.5906161,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.5906161, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);			
	}
	
	/**
	 * 说明：
	 * 	Bool查询对其他查询结果进行boolean组合。类似与lucene的BooleanQuery。使用一个或多个boolean clause
	 	must：clause必须包含在内
	 	should：clause至少一个被包含在内，通过minimum_number_should_match参数设置最小clause匹配数
	 	must_not: clause不能包含在内
	 *  Bool查询也支持disable_coord参数（默认false），评分机制
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testBoolQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
		    "query" : {
			    "bool" : {
			        "must" : {
			            "term" : { "user" : "kimchy2" }
			        },
			        "must_not" : {
			            "range" : {
			                "postDate" : { "from" : "2009-11-15T14:12:10", "to" : "2009-11-15T14:12:11" }
			            }
			        },
			        "minimum_number_should_match" : 1,
			        "boost" : 1.0
			    }
			}
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);		
	}	

	/**
	 * 说明：
	 * 	boosting查询用于对结果进行降权，对降权的细节算法不太明白
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testBoostingQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query" : 
			{
				"boosting" : {
					"positive" : {
						"term" : {
							"user" : "kimchy2"
						}
					},
					"negative" : {
						"term" : {
							"user" : "kimchy2"
						}
					},
					"negative_boost" : 0.2
				}
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.2,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.2, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	ids查询用于根据提供的id进行批量查询
	 * 前提：
	 * 	建立索引，不需要_id字段建索引，因为ids是工作在内部_uid字段上
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testIdsQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query" : 
			{
				"ids" : {
					"type" : "index",
					"values" : ["1", "2"]
				}
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}},{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	custom_score允许自定义评分，可以使用 脚本表达式 来根据文档查询结果中(数值型)的值计算评分，
	           可以使用_score参数来获取其评分，可以使用params设置脚本参数
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testCustomScoreQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		  "query": {
		    "custom_score": {
		      "script":"_score * param1" ,
			  "params" : {
				"param1" : 2
			  },
		      "query": {
				"bool" : {
					"must" : {
						"term" : {"user" : "kimchy2" }
					}
				}
		      }
		    }
		  },
		  "size": 1
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":2.0,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":2.0, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	custom_boost_factor允许自定义评分，计算方式为其参数boost_factor乘以原来的评分
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */
	public function testCustomBoostFactor() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		  "query": 
			{
			    "custom_boost_factor": {
			        "query" : {
			            "term" : { "user" : "kimchy2"}
			        },
			        "boost_factor": 3.5
			    }
			},
			  "size": 1
			}
		';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":3.5,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":3.5, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 没看明白有什么用
	 */
	public function testConstantScore() {
		
	}
	
	/**
	 * 还在搞清楚中
	 */
	public function testDisMaxQuery() {
		
	}
	
	/**
	 * 说明：
	 * 	是query_string的简化版，大部分query_string的参数都可以在这里用
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回一条匹配记录
	 */	
	public function testFieldQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
			"query":
			{
			    "field" : { 
			        "message" :"+trying -Search2 -Search3 -Search4 -Search5 -Search6 -Search7 -Search8 -Search9 -Search10"
			    }
			}
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.2972674,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.2972674, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);		
	}
	
	/**
	 * 说明：
	 * 	FilteredQuery，对查询结果进行过滤，这里两层过滤后应该搜不出任何结果
	 *  filter对象只能是filter的元素，不能是查询语句。filter与查询相比更快（尤其在cache的时候），因为省去了评分的过程
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回0条匹配记录
	 */
	public function testFilterQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "filtered" : {
			        "query" : {
			            "term" : { "message" : "trying" }
			        },
			        "filter" : {
			            "range" : {
			                "postDate" : { "from" : "2009-11-15T14:12:10", "to" : "2009-11-15T14:12:11" }
			            }
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":0,"max_score":null,"hits":\[\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
}

