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
				"location" : {"lat" : 40.1' . $i . ', "lon" : -71.3' . $i . '}			    		
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":4,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"4","_score":1.0, "_source" : {
				"user" : "kimchy4",
 				"postDate" : "2009-11-15T14:12:12",						
			    "message" : "trying out Elastic Search4" ,
			    "age" : 4,
		        "location" : { 
		            "lat" : 40.14, 
		            "lon" : -71.34 
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
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
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
	
	/**
	 * 说明：
	 * geo distance filter，计算和指定的一个坐标的距离，按距离进行过滤，过滤出距离以内的坐标点。
	 * 这里过滤出1公里以内的坐标点。
	 * 在指定参考坐标时，这里采用的是properties方式
	        参数：
	   distance:
	   distance_type:默认arc，精度比较高。或者plane（速度比较快）
	   optimize_bbox:默认memory,indexed,noe
	 * 前提：
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoDistanceAsPropertiesFilter() {
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
			            "geo_distance" : {
			                "distance" : "1km",
			                "index.location" : {
			                    "lat" : 40.11,
			                    "lon" : -71.31
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	 * 说明：
		同上，不过这里采用的是array方式指定坐标，坐标格式参照 GeoJSON，这里为[lon, lat]
	 * 前提：
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoDistanceAsArrayFilter() {
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
			            "geo_distance" : {
			                "distance" : "1km",
			                "index.location" : [-71.31, 40.11]
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	 * 说明：
	 同上，不过这里采用的是string方式指定坐标，坐标格式为"lat, lon"，和array顺序相反
	 * 前提：
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoDistanceAsStringFilter() {
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
			            "geo_distance" : {
			                "distance" : "1km",
			                "index.location" : "40.11, -71.31"
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	 * 说明：
	       也是需要计算距离，不过将距离限定在一个闭区间范围内
	       支持range的其他参数，比如lt, lte, gt, gte, from, to, include_upper and include_lower)
	 * 前提：
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoDistanceRangeFilter() {
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
			            "geo_distance_range" : {
			                "from" : "1km",
			                "to" : "2km",
			                "index.location" : {
			                    "lat" : 40.11,
			                    "lon" : -71.31
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

	/**
	 * 说明：
	        给定一群点坐标，构成一个多边形。搜索落在多边形内的坐标。坐标的格式同样支持properties、array、string和geohash四种方式
	 * 前提：
	 *   必须在mapping中明确指定坐标字段（比如location）类型是geo_point
	 * 结果：
	 *   返回区域范围内的坐标文档
	 */
	public function testGeoPolyganFilter() {
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
			            "geo_polygon" : {
			                "index.location" : {
			                    "points" : [
			                        {"lat" : 41.15, "lon" : -70.35},
			                        {"lat" : 30, "lon" : -80},
			                        {"lat" : 20, "lon" : -90}
			                    ]
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	 * 有待进一步了解
	 * 
	 */
	public function testGeoShapeFunction(){
		
	}
	
	/**
	 * 比较简单，不举例了
	   {
		    "constant_score" : {
		        "filter" : {
		            "match_all" : { }
		        }
		    }
		}
	 */
	public function testMatchAllFilter() {
		
	}
	
	/**
	 * 说明：
	        和Exist filter相反，这里查询字段内容为空的记录。这个过滤器结果总是缓存的。
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testMissingFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "constant_score" : {
			        "filter" : {
			            "missing" : { 
			                "field" : "message",
			                "existence" : true,
			                "null_value" : true
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"5","_score":1.0, "_source" : {
				"user" : "kimchy6",
 				"postDate" : "2009-11-15T14:12:12",
			    "message" : "" ,
			    "age" : 6,
				"location" : {"lat" : 40.16, "lon" : -71.36}			    		
			}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 Not filter将不匹配的记录筛选出来。比bool filter效率更高。
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testNotFilter() {
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
			            "not" : {
			                "range" : {
			                    "age" : {
			                        "from" : 2,
			                        "to" : 6
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
	 * 说明：
	   Numeric Range Filter和range filter类似，不过只作用于数值类型，另外工作机制也有所不同。
	   Numeric range filter将所有相关字段值加载到内存，检查关联文档是否在范围中。因此这里需要
	       更多内存，因为范围内数据都需要加载的内存，但是能够带来效率上的明显提升。
	       参数：
	   from:
	   to:
	   include_lower:
	   include_upper:
	   gt:
	   gte:
	   lt:
	   lte:
	   这个用例搜索[1,2)半开半闭区间的索引
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testNumericRangeFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "constant_score" : {
			        "filter" : {
			            "numeric_range" : {
			                "age" : { 
			                    "from" : "1", 
			                    "to" : "2", 
			                    "include_lower" : true, 
			                    "include_upper" : false
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0, "_source" : {
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
	 * 说明：
	   Or filter，性能优于bool filter	  
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testOrFilter() {
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
			            "or" : [
			                {
			                    "term" : { "user" : "kimchy1" }
			                },
			                {
			                    "term" : { "user" : "kimchy2" }
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
	 Prefix filter，不分词。默认开启缓存
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testPrefixFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "constant_score" : {
			        "filter" : {
			            "prefix" : { "user" : "kimchy1" }
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
	 * 说明：
	   将任意查询用作filter
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testQueryFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "constantScore" : {
			        "filter" : {
			            "query" : { 
			                "query_string" : { 
			                    "query" : "trying AND search2"
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
	
	/**
	 * 类似于testNumericRangeFilter，只需把numeric_range换成range也默认启用缓存
	 */
	public function testRangeFilter() {
		
	}
	
	/**
	 * 说明：
	    将脚本作为过滤器。脚本会被编译并且缓存，以保证高效执行。如果同样的脚本被再次调用，只是参数不同，
	    建议使用脚本的参数功能
	    这里用例在message含有trying的结果中只显示年龄>3的记录
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testScriptFilter() {
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
			            "script": {
			               "script" : "doc[\'age\'].value > param1",
				           "params" : {
							 "param1" : 3
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
	   同term query类似，不分词。
	 * 前提：
	 *   存在对应索引
	 * 结果：
	 *   返回json文档
	 */
	public function testTermFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
			"query":
			{
			    "constant_score" : {
			        "filter" : {
			            "term" : { "user" : "kimchy1"}
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
	 * 同terms query类似
	 */
	public function testTermsFilter() {
		
	}
	
	/**
	 *类似于NestedQuery，语法如下
	{
	"query":
		{
		    "filtered" : {
		        "query" : { "match_all" : {} },
		        "filter" : {
		            "nested" : {
		                "path" : "obj1",
		                "query" : {
		                    "bool" : {
		                        "must" : [
		                            {
		                                "text" : {"obj1.name" : "blue"}
		                            },
		                            {
		                                "range" : {"obj1.count" : {"gt" : 5}}
		                            }
		                        ]
		                    }
		                },
		                "_cache" : true
		            }
		        }
		    }
		}
	}
	 */	
	public function testNestedFilter() {
		
	}
}


