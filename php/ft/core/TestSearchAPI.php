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
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		
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
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
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
	
	/**
	 1.	Query And Fetch
	 	eg. http://10.232.42.205:9200/test/index/_search?q=*&search_type=query_and_fetch
	 	最简单（可能是最快）的实现方式：只需要在所有相关分片上执行query，再返回结果即可。每个分片根据size参数，返回指定数量的结果。
	 	查询结果总数就是size*分片数量。
	 2.	Query Then Fetch
	 	也是在所有分片上查询，不过只返回一定数量的信息（不是文档内容）。这些返回结果进行排序、算分等等，只有相关分片才会要求实际的文档内容。
	 	通过size指定返回结果的总数量，只有这些才是需要获取实际文档内容的。如果一份索引包含许多分片时（不是副本），这种方式非常有用。
	 3. Dfs, Query And Fetch
	 	和"Query And Fetch"一样，只不过在初始化请求分发的阶段，进行了分布式的词频计算来保证更加精准的打分。	
	 4. Dfs, Query Then Fetch
	 	和"Query Then Fetch"一样，只不过在初始化请求分发的阶段，进行了分布式的词频计算来保证更加精准的打分。	
	 5. Count
		一个特殊的查询类型，返回结果总数，如果可能，也会返回facets总数	 
	 6. Scan
	 	在大数据集上进行高效的遍历。
	 	先用下面这个查询进行scan，其中size参数控制每次scroll文档数，scroll参数控制scroll数据存活期，以及初始化scroll进程：
	 	curl -XGET 'localhost:9200/_search?search_type=scan&scroll=10m&size=50' -d ' 
		{ 
		    "query" : { 
		        "match_all" : {} 
		    } 
		} 
		这个请求不会包含实际的数据，只有total_hits统计的hits数，scroll_id保存scroll进程的标记。
		接下来可以用如下的查询，根据scroll_id拿到实际的结果
		curl -XGET 'localhost:9200/_search/scroll?scroll=10m' -d 'c2NhbjsxOjBLMzdpWEtqU2IyZHlmVURPeFJOZnc7MzowSzM3aVhLalNiMmR5ZlVET3hSTmZ3OzU6MEszN2lYS2pTYjJkeWZVRE94Uk5mdzsyOjBLMzdpWEtqU2IyZHlmVURPeFJOZnc7NDowSzM3aVhLalNiMmR5ZlVET3hSTmZ3Ow=='
		
	 * 
	 */
	public function testSearchType() {
		
	}
	
	/**
	 * 在一次查询中给不同的index赋予不同的boost值
	 eg. curl -XGET "http://10.232.42.205:9200/_search" -d '
	 { 
	 	"indices_boost": {"test": 1.2, "blog": 1.1},
		"query":{ "match_all" :{}}
	 }'
	 */
	public function testIndicesBoost() {
		
	}
	
	/**
	 * 给出如何评分的详细过程
	 */
	public function testExplain() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
		
		$query = '{ 
		    "explain": true, 
		    "query" : { 
		        "term" : { "tag": "green" } 
		    } 
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_shard":3,"_node":"B5cyxUnMQC64JnNfhl16kw","_index":"test","_type":"index","_id":"2","_score":0.30685282, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		},"_explanation":{"value":0.30685282,"description":"fieldWeight\(tag:green in 0\), product of:","details":\[{"value":1.0,"description":"tf\(termFreq\(tag:green\)=1\)"},{"value":0.30685282,"description":"idf\(docFreq=1, maxDocs=1\)"},{"value":1.0,"description":"fieldNorm\(field=tag, doc=0\)"}\]}}\]}}';
		$this->AssertRegExp($expected, $result, $result);		
	}
	
	/**
	 *加上version=true，可以返回每条索引的版本号
	 */
	public function testReturnVersion() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "version": true, 
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
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.30685282,"hits":\[{"_index":"test","_type":"index","_id":"2","_version":1,"_score":0.30685282, "_source" : {
		    "message" : "something green",
		    "tag" : "green"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
	
	/**
	 * 说明：
	 	过滤掉小雨min_score的记录。这里将低于0.1得分的记录给过滤掉。
	 * 前提：
	 * 	存在该的索引
	 * 判断：
	 * 	1.	返回json索引
	 */
	public function testMinScoreFilter() {
		$method = "GET";
		$url = "http://10.232.42.205/test/index/_search";
	
		$query = '{ 
		    "min_score": 0.1, 
		    "query" : { 
		        "query_string" : { "query" : "something blue" } 
		    } 
		}';
			
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
	
		$expected = '{"took":[\d]{1,2},"timed_out":false,"_shards":{"total":5,"successful":5,"failed":0},"hits":{"total":1,"max_score":0.26191524,"hits":\[{"_index":"test","_type":"index","_id":"1","_score":0.26191524, "_source" : {
		    "message" : "something blue",
		    "tag" : "blue"
		}}\]}}';
		$this->AssertRegExp($expected, $result, $result);
	}	
}

