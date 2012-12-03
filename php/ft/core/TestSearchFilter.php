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
class TestSearchFilter extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/_mapping";
		
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"},
                    "user" : {"type" : "string", "store" : "no"},
                    "postDate" : {"type" : "date", "store" : "no"},
				    "age" : {"type" : "long", "store" : "no"},
            		"location" : {"type" : "geo_point"}				
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
		for($i=1; $i<=5; $i++) {
			$url = "http://10.232.42.205/test/index/" . $i;
						
			$index = '{
				"user" : "kimchy' . $i . '",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search' . $i . '" ,
			    "age" : ' . $i . ',
		        "location" : { 
		            "lat" : 40.1' . $i .', 
		            "lon" : -71.3' . $i . ' 
		        }
			}';
			
			curl_setopt(self::$ch, CURLOPT_URL, $url);
			curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
			
			$result = curl_exec(self::$ch);		
		}	
		$index = '{
				"user" : "kimchy' . $i . '",
 				"postDate" : "2009-11-15T14:12:12",
			    "message" : "" ,
			    "age" : ' . $i . ',
				"location" : {"lat" : 40.1' . $i . ', "lon" : -71.3 ' . $i . '}			    		
			}';		
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
			
		$result = curl_exec(self::$ch);
				
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
          And操作符过滤查询结果，这个filter性能要好于bool filter。
                           默认过滤结果不被缓存，使用_cache参数可以启用缓存（通常情况下是没有必要的）。是否缓存，查询语法略有不同。
	 *  
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testAndFilter() {
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
			            "and" : [ 
			                { 
			                    "range" : {  
			                        "postDate" : {  
			                            "from" : "2009-11-15T14:12:12", 
			                            "to" : "2009-11-15T14:12:12" 
			                        } 
			                    } 
			                }, 
			                { 
			                    "prefix" : { "user" : "kimchy" } 
			                } ,
			                { 
			                    "range" : {  
			                        "age" : {  
			                            "from" : "1", 
			                            "to" : "2" 
			                        } 
			                    } 
			                }                   
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
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":0.15342641,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.15342641, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" ,
			    "age" : 1,
		        "location" : { 
		            "lat" : 40.11, 
		            "lon" : -71.31 
		        }
			}},{"_index":"test","_type":"index","_id":"2","_score":0.15342641, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" ,
			    "age" : 2,
		        "location" : { 
		            "lat" : 40.12, 
		            "lon" : -71.32 
		        }
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);	
	}
	
	/**
	 * 说明：
	   Bool过滤
	 *
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testBoolFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{ 
			    "filtered" : { 
			        "query" : { 
			            "queryString" : {  
			                "default_field" : "message",  
			                "query" : "elastic" 
			            } 
			        }, 
			        "filter" : { 
			            "bool" : { 
			                "must" : { 
			                    "term" : { "user" : "kimchy4" } 
			                }, 
			                "must_not" : { 
			                    "range" : { 
			                        "age" : { "from" : 2, "to" : 3 } 
			                    } 
			                }, 
			                "should" : [ 
			                    { 
			                        "term" : { "message" : "search2" } 
			                    }, 
			                    { 
			                        "term" : { "message" : "search4" } 
			                    } 
			                ] 
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.15342641,"hits":\[{"_index":"test","_type":"index","_id":"4","_score":0.15342641, "_source" : {
				"user" : "kimchy4",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search4" ,
			    "age" : 4,
		        "location" : { 
		            "lat" : 40.14, 
		            "lon" : -71.34 
		        }
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}
		
	/**
	 * 说明：
	 Exist过滤，把字段值为空的查询结果过滤掉
	 *
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testExistFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{ 
			    "constant_score" : { 
			        "filter" : { 
			            "exists" : { "field" : "message" } 
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":5,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"4","_score":1.0, "_source" : {
				"user" : "kimchy4",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search4" ,
			    "age" : 4,
		        "location" : { 
		            "lat" : 40.14, 
		            "lon" : -71.34 
		        }
			}},{"_index":"test","_type":"index","_id":"5","_score":1.0, "_source" : {
				"user" : "kimchy5",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search5" ,
			    "age" : 5,
		        "location" : { 
		            "lat" : 40.15, 
		            "lon" : -71.35 
		        }
			}},{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" ,
			    "age" : 1,
		        "location" : { 
		            "lat" : 40.11, 
		            "lon" : -71.31 
		        }
			}},{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" ,
			    "age" : 2,
		        "location" : { 
		            "lat" : 40.12, 
		            "lon" : -71.32 
		        }
			}},{"_index":"test","_type":"index","_id":"3","_score":1.0, "_source" : {
				"user" : "kimchy3",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search3" ,
			    "age" : 3,
		        "location" : { 
		            "lat" : 40.13, 
		            "lon" : -71.33 
		        }
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 语法同ids query
	 */
	public function testIdsFilter() {
	}	
	
	/**
	 * 说明：
	 * 限制每个shard上查询的文档数
	 */
	public function testLimitFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
			"query":
			{ 
			    "filtered" : { 
			        "filter" : { 
			             "limit" : {"value" : 100} 
			         }, 
			         "query" : { 
			            "term" : { "message" : "search1" } 
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

		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.15342641,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.15342641, "_source" : {
				"user" : "kimchy1",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search1" ,
			    "age" : 1,
		        "location" : { 
		            "lat" : 40.11, 
		            "lon" : -71.31 
		        }
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);		
	}	
	
	/**
	 * 筛选type
	 { 
	    "type" : { 
	        "value" : "my_type" 
	    } 
	}
	 */
	public function testTypeFilter() {
		
	}
	
	/**
	 * 说明：
	 * geo bouding box按地理坐标进行过滤，从左上（上纬度，左精度）至右下（下纬度，右精度）
	 * 前提：
	 *   必须在mapping中明确指定字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoBoundingBoxFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{ 
			    "filtered" : { 
			        "query" : { 
			            "match_all" : {} 
			        }, 
			        "filter" : { 
			            "geo_bounding_box" : { 
			                "index.location" : { 
			                    "top_left" : { 
			                        "lat" : 40.12, 
			                        "lon" : -71.32 
			                    }, 
			                    "bottom_right" : { 
			                        "lat" : 40.12, 
			                        "lon" : -71.31
			                    } 
			                } 
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":1.0, "_source" : {
				"user" : "kimchy2",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search2" ,
			    "age" : 2,
		        "location" : { 
		            "lat" : 40.12, 
		            "lon" : -71.32 
		        }
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
}

