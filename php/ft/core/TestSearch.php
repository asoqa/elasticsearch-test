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
		
		$expected = '{"took":[\d]{1,3},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	
	/**
	 * 说明：
	 * 	Fuzzy Like this可以在一个或多个字段中进行like查询，可以缩写为flt，支持的参数有：
	    fields:
	    like_text:
	    ignore_tf:
	    max_query_terms:
	    min_similarity:
	    prefix_length:
	    boost:
	    analyzer:
	 *  对所有term进行查询，挑出最好的n个不同term的结果.FuzzyQuery和MoreLikethis的混合体，不过需要额外考虑 一些模糊评分因子。
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回0条匹配记录
	 */
	public function testFuzzyLikeThis() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "fuzzy_like_this" : {
			        "fields" : ["user", "message"],
			        "like_text" : "kimchy1",
			        "max_query_terms" : 10,
			        "prefix_length" : 7
			    }
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,3},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}},{"_index":"test","_type":"index","_id":"10","_score":1.0, "_source" : {
				"user" : "kimchy10",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search10" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 在child索引中查找符合query的文档，返回其对应的parent文档。
	 * 同has_child filter的用法，参考TestIndex.php的testParentChild测试用例。注意type是child的type。
	 * 这个查询比较耗内存，因为会把所有_id取出来，放在内存里
	 */
	public function testHasChildQuery() {
		
	}
	
	/**
	 * 在parent索引中查找符合query的文档，返回其对应的child文档
	 * 用法同has_parent_filter，在0.19.10后版本可用，具体用例同testHasChildQuery。
	 */
	public function testHasParentQuery() {
		
	}
	
	/**
	 * 说明：
	 * 	同lucene的MatchAllQuery，取出所有文档， 默认boost值都是1.0
	 *  可以通过"match_all" : { "norms_field" : "my_field" }，使得index boost生效。不过貌似没什么用，不太清楚
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */
	public function testMatchAllQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
			    "match_all" : { }
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = file_get_contents(__DIR__ . "\\TestSearch_testMatchAllQuery.json");
		$this->AssertRegExp($expected, $result, $result);	
	}	
	
	/**
	 * 说明：
	 * 	more like this查询空可以在多个字段中查找相似记录，可以缩写成mlt。支持参数：
	    fields:
	    like_text:
	    percent_terms_to_match:
	    min_term_freq:
	    max_query_terms:
	    stop_words:
	    min_doc_freq:
	    max_doc_freq:
	    min_word_len:
	    max_word_len:
	    boost_terms:
	    boost:
	    analyzer:
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */
	public function testMoreLikeThisQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "more_like_this" : {
			        "fields" : ["user"],
			        "like_text" : "kimchy1",
			        "min_term_freq" : 1,
			        "min_doc_freq" : 1, 
			        "max_query_terms" : 12
			    }
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
	 * 说明：
	 * 	和more like this一样，不过是针对单个字段。语法近似于DSL。可以缩写成mlt_field
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */	
	public function testMoreLikeThisFieldQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "more_like_this_field" : {
			        "user" : {
			            "like_text" : "kimchy1",
			            "min_term_freq" : 1,
			            "min_doc_freq" : 1, 
			            "max_query_terms" : 12
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	前缀匹配（不进行分词），等同于Lucene的PrefixQuery。可以加boost
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */
	public function testPrefixQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "prefix" : { "user" : "kimchy1" }
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
			}},{"_index":"test","_type":"index","_id":"10","_score":1.0, "_source" : {
				"user" : "kimchy10",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search10" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}

	/**
	 * 说明：
	 * 	用query parser对查询字符串进行分词，再查询。支持的参数为：
	    query:
	    default_field:默认为_all
	    analyzer:查询字符串的 analyzer
	    allow_leading_wildcard:
	    lowercase_expanded_terms:
	    enable_position_increments:
	    fuzzy_prefix_length:
	    fuzzy_min_sim:
	    phrase_slop:
	    boost:
	    analyze_wildcard:
	    auto_generate_phrase_queries:
	    minimum_should_match:
	    lenient:
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */
	public function testQueryStringQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "query_string" : {
			        "default_field" : "message",
			        "query" : "trying AND Search1 OR out"
			    }
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.65325016,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.65325016, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}

	/**
	 * 说明：
	 * 	多字段查询。组合多字段查询的方式或者是dis_max或者是bool
	    user^5表示user的boost值设为5
	    fields字段可以用通配符，比如"fields" : ["city.*"],
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回所有匹配记录
	 */	
	public function testQueryStringQueryMultiField() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "query_string" : {
			        "fields" : ["message", "user^5"],
			        "query" : "trying AND Search1 OR kimchy1",
			        "use_dis_max" : true
			    }
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.43753055,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.43753055, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	RangeQuery。string类型的字段走TermRangeQuery，数值/日期的字段
	 *  走NumericRangeQuery。这里用例查询两个日期之间的记录，没有匹配上的。
	    from:
	    to:
	    include_lower:
	    include_upper:
	    gt:
	    gte:
	    lt:
	    lte:
	    boost:
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回0匹配记录
	 */
	public function testRangeQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
				{
				    "range" : {
				        "postDate" : { 
				            "from" : "2009-11-15T14:12:10", 
				            "to" : "2009-11-15T14:12:11", 
				            "include_lower" : true, 
				            "include_upper": false, 
				            "boost" : 2.0
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
	
	/**
	 * 说明：
	 * 	SpanFirstQuery, 查找方式为从Field的内容起始位置开始，在一个固定的宽度内查找所指定的词条。
	    end参数用来指定查找宽度。
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回匹配记录
	 */
	public function testSpanFirstQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{
			    "span_first" : {
			        "match" : {
			            "span_term" : { "user": "kimchy1" }
			        },
			        "end" : "1"
			    }
			}   
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.70710677,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.70710677, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	SpanNearQuery, 类似于phrase query，对词组按slop位移进行整体匹配。但是SpanNearQuery可以单独设置词条的顺序，因为记录下了位置，所以可以正着查，可以反着查
	          比如trying和search2，在slop为2的情况下，可以在两个单词之间允许存在1或2个其他单词
	    spannearquery能够查到该记录。
	    
	    slop参数用来控制不匹配的位置数量
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回匹配记录
	 */
	public function testSpanNearQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query": 
			{ 
			    "span_near" : { 
			        "clauses" : [ 
			            { "span_term" : { "message": "trying" } }, 
			            { "span_term" : { "message" : "search2" } }
			        ], 
			        "slop" : 2, 
			        "in_order" : true, 
			        "collect_payloads" : false 
			    } 
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.35654885,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.35654885, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	Span_not查询。
	          在include的词组跨度中排除exclude的词组，并非从在include的查询结果中排除exclude的查询结果，切记切记。
	          比如这里include中span_near的trying和search2查询跨度为2（中间隔了out和elastic两个term），结果有1条匹配
	           exclude中search2不再include跨度范围的terms(out和elastic)之中，所以不影响结果，仍然是1条匹配
	          反之，如果exclude中term为out，则在跨度范围内，这部分结果需要被排除，因此没有任何结果匹配
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回匹配记录
	 */
	public function testSpanNotQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "span_not" : {
			        "include" : {
			        "span_near" : { 
			        "clauses" : [ 
			            { "span_term" : { "message": "trying" } }, 
			            { "span_term" : { "message" : "search2" } }
			        ], 
			        "slop" : 2, 
			        "in_order" : true, 
			        "collect_payloads" : false 
			    } 
			        },
			        "exclude" : {
			            "span_term" : { "message" : "search3" }
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
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.35654885,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.35654885, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);		
	}	
	
	/**
	 * 说明：
	 * 	SpanOrQuery很简单，就是两个查询子句的并集。
	  
	 slop参数用来控制不匹配的位置数量
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回匹配记录
	 */
	public function testSpanOrQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "span_or" : {
			        "clauses" : [
			            { "span_term" : { "message" : "search2"} },
			            { "span_term" : { "message" : "search3"} }
			        ]
			    }
			}
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.2388785,"hits":\[{"_index":"test","_type":"index","_id":"3","_score":1.2388785, "_source" : {
				"user" : "kimchy3",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search3" 
			}},{"_index":"test","_type":"index","_id":"2","_score":0.9521713, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 * 	SpanTermQuery根据term查询，是其他span查询的基础。功能与TermQuery类似，但是会额外记录下term
	  	在每个文档出现的位置
	 * 前提：
	 * 	建立索引
	 * 判断：
	 * 	1.返回匹配记录
	 */
	public function testSpanTermQuery() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "span_term" : { "user" : "kimchy1" }
			}   
		}';
	
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.70710677,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.70710677, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" 
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
}

