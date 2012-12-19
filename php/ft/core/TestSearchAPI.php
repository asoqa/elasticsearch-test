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
class TestSearchAPI extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		//建索引库
		$method = "PUT";
		$url = "http://10.232.42.205/test";		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		$result = curl_exec(self::$ch);

		//定义map
		$url = "http://10.232.42.205/test/index/_mapping";
		$map = '{
		    "index" : {
		        "properties" : {
		            "message" : {"type" : "string", "store" : "yes"},
                    "tag" : {"type" : "string", "store" : "no"}
		        },
				"_timestamp" : { "enabled" : true },
				"_ttl" : { "enabled" : true }					
		    }
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $map);
		$result = curl_exec(self::$ch);		
		
		//准备测试数据
		$method = "PUT";
		$url = "http://10.232.42.205/test/index/1";
		$index = '{
		    "message" : "something blue",
		    "tag" : "blue"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $index);
		$result = curl_exec(self::$ch);
		
		$url = "http://10.232.42.205/test/index/2";
		$index = '{
		    "message" : "something green",
		    "tag" : "green"
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
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
	 * 	这里的用例过滤出tag=green的记录
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testFacets() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{
		    "query" : {
		        "term" : { "message" : "something" }
		    },
		    "filter" : {
		        "term" : { "tag" : "green" }
		    },
		    "facets" : {
		        "tag" : {
		            "terms" : { "field" : "tag" }
		        }
		    }
		}';
		
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.19178301,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.19178301, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		}}\]},"facets":{"tag":{"_type":"terms","missing":0,"total":2,"other":0,"terms":\[{"term":"green","count":1},{"term":"blue","count":1}\]}}}';
		$this->AssertRegExp($expected, $result, $result);	
	}
	
	/**
	 * 说明：
	 *  from定义起始索引序号，size定义返回的记录数
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testFromSize() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "from" : 0, "size" : 1,
		    "query" : {
		        "term" : { "message" : "something" }
		    }
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":0.19178301,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.19178301, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  指定在不同的type中进行查询。这在index和index-child下进行查询。
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchAcrossDifferentTypes() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index,index-child/_search?q=message:something";
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":0.19178301,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.19178301, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		}},{"_index":"test","_type":"index","_id":"2","_score":0.19178301, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  指定在不同的indices中进行查询。这在index和index-child下进行查询。
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchAcrossDifferentIndices() {
		$method = "GET";
		$url = "http://10.232.42.205/test,twitter/index,index-child/_search?q=message:something";
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":10,"successful":10,"failed":0},"hits":{"total":2,"max_score":0.19178301,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.19178301, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		}},{"_index":"test","_type":"index","_id":"2","_score":0.19178301, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  _all指定在所有indices中查询。貌似type不能用_all
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchAllIndices() {
		$method = "GET";
		$url = "http://10.232.42.205/_all/index,index-child/_search?q=message:something";
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":46,"successful":46,"failed":0},"hits":{"total":2,"max_score":0.19178301,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.19178301, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		}},{"_index":"test","_type":"index","_id":"2","_score":0.19178301, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  对搜索结果的一个或多个字段进行highlight，在搜索结果中会把highlight字段独立出来显示，并默认用<em></em>标记
	 *  为了能够做highlight，字段的实际内容是必须要拿到的。如果字段设为stored，则直接从store中获取；否则，_source会被加载
	 *  进来，并从相关字段中抽取出所需的信息
	 *  
	 *  如果term_vector没有设置（设term_vector=with_postions_offsets），则使用普通的highlighter。否则，使用快速的vector highlighter。
	 *  通常情况下，后者更加高效，不过会增大索引
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testHighlight() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "query" : {"term":{"tag":"blue"}},
		    "highlight" : {
		        "fields" : {
		            "message" : {}
		        }
		    }
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.30685282, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		},"highlight":{"message":\["something <em>blue</em>"\]}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}

	/**
	 * 说明：
	 *  不是用默认的标记 <em></em>，这里改为用<tag1></tag1>
	 *  还可以使用encoder参数(default|html)来定义highlighted文本如何编码
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testHighlightTags() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{
		    "query" : {"term":{"tag":"blue"}},
		    "highlight" : {
		        "pre_tags" : ["<tag1>", "<tag2>"],
		        "post_tags" : ["</tag1>", "</tag2>"],
		        "fields" : {
		            "message" : {}
		        }
		    }
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.30685282, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		},"highlight":{"message":\["something <tag1>blue</tag1>"\]}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 从字面意义上看,fragment_size是设置被高亮的分片最大字符数，number_of_fragments限制高亮片段的数量
	 * 但是从实际试验结果看，又不完全是这样，存疑。。。
	 */
	public function testHighlightSettings() {
		$this->assertFalse(true);
	}
	
	/**
	 * 说明：
	 *  通过fields限定查询结果显示的字段，默认从_source中加载，除非指定该字段store为yes
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchFields() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "fields" : ["message"], 
		    "query" : { 
		        "term" : { "tag" : "green" } 
		    } 
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.30685282,"fields":{"message":"something green"}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 *  如果fields为空，则查询结果中只返回id和type
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchFieldsEmpty() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "fields" : [], 
		    "query" : { 
		        "term" : { "tag" : "green"} 
		    } 
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_index":"test","_type":"index","_id":"2","_score":0.30685282}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * partial_fields对嵌套对象进行部分抽取
	 { 
	    "query" : { 
	        "match_all" : {} 
	    }, 
	    "partial_fields" : { 
	        "partial1" : { 
	            "include" : ["obj1.obj2.*", "obj1.obj4.*"], 
	            "exclude" : "obj1.obj3.*" 
	        } 
	    } 
	}
	 */
	public function testSearchPartialField() {
		
	}
	
	/**
	 * 说明：
	 *  可以在fields上应用 script脚本改变其中的值。script字段可以从实际的_source文档中抽取特定的元素。
	 *  比如_source.obj1.obj2
	          理解doc['my_field'].value和_source.my_field的差异非常重要：
	          首先：使用doc关键字，会使字段对应的terms被加载到内存中（被缓存），这样执行起来更快，但是需要消耗更多的内存
                     其次：doc[]语法仅仅对简单的字段类型有效（例如不能返回json对象），并且只针对不分词的字段，或单term字段。
        _source会使得原文档被加载、分析，然后返回json的相关部分 。      	          
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testSearchScriptFields() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "query" : { 
		        "match_all" : {} 
		    }, 
		    "script_fields" : { 
		        "message" : { 
		            "script" : "doc[\'message\'].values.length" 
		        }, 
		        "tag" : { 
		            "script" : "doc[\'tag\'].values.length * factor", 
		            "params" : { 
		                "factor"  : 4.0 
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":2,"max_score":1.0,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":1.0,"fields":{"message":2,"tag":4.0}},{"_index":"test","_type":"index","_id":"2","_score":1.0,"fields":{"message":2,"tag":4.0}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * perference可以设为
	 * 1. _primary:仅仅在主分片上执行搜索
	 * 2. _primary_first:优先主分片
	 * 3. _local: 优先本地
	 * 4. _only_node:xyz ：在指定node上
	 * 5. custom value:自定的值
	 */
	public function testPreference() {
		
	}
}

