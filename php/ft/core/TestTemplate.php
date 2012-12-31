<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/admin-indices-templates.html
 * 说明:
 *   index template的使用
 * @author 	can.zhaoc
 *
 */
class TestTemplate extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
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
		$url = "http://10.232.42.205/te1";
		
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
	 *  索引模板可以自动的应用于新建的索引库上。模板包括settings和mappings。这里定义了一个template_1模板，模板类型为te*，
	 *  这意味着所有以te*开头的索引库将使用template_1的模板。
	 * 前提：
	 * 	
	 * 判断：
	 * 	1.	返回的验证结果
	 */
	public function testCreateTemplate() {
		
		//创建模板
		$method = "PUT";
		$url = "http://10.232.42.205/_template/template_1";
		$query = '{
		    "template" : "te*",
		    "settings" : {
		        "number_of_shards" : 1
		    },
		    "mappings" : {
		        "type1" : {
		            "_source" : { "enabled" : false }
		        }
		    }
		}';
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_PORT, 9200);
		curl_setopt(self::$ch, CURLOPT_RETURNTRANSFER, 1);		
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, $query);
		$result = curl_exec(self::$ch);
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);	

		//创建符合模板类型的索引库te1
		$method = "PUT";
		$url = "http://10.232.42.205/te1";
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");		
		$result = curl_exec(self::$ch);
		
		//验证模板
		$method = "GET";
		$url = "http://10.232.42.205/te1/_settings";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
		$expected = '{"te1":{"settings":{"index.number_of_shards":"1","index.number_of_replicas":"1","index.version.created":"[\d]+"}}}';
		$this->assertRegExp($expected, $result, $result);
		
		$url = "http://10.232.42.205/te1/_mapping";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
		$expected = '{"te1":{"type1":{"_source":{"enabled":false},"properties":{}}}}';
		$this->assertRegExp($expected, $result, $result);		

		//删除模板
		$method = "DELETE";
		$url = "http://10.232.42.205/_template/template_1";
		curl_setopt(self::$ch, CURLOPT_URL, $url);
		curl_setopt(self::$ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt(self::$ch, CURLOPT_POSTFIELDS, "");
		$result = curl_exec(self::$ch);
		$expected = '{"ok":true,"acknowledged":true}';
		$this->assertEquals($expected, $result, $result);
	}
	
	/**
	 * 有可能出现一个索引符合多个模板类型的情况，这时候可以在template json中加上order参数，指定模板匹配顺序。
	 * order值小的先应用，order值高的后应用，后应用的覆盖前面的
	 * 
	 */
	public function testMultiTemplate() {
		
	}
	
	/**
	 * 索引模板可以放在config/templates目录下，建立template_1.json的文件。注意必须在所有节点的config/template目录下
	 建立同样的文件。文件格式如下：
	{
	    "template_1" : {
	        "template" : "*",
	        "settings" : {
	            "index.number_of_shards" : 2
	        },
	        "mappings" : {
	            "_default_" : {
	                "_source" : {
	                    "enabled" : false
	                }
	            },
	            "type1" : {
	                "_all" : {
	                    "enabled" : false
	                }
	            }
	        }
	    }
	}
	 */
	public function testConfigTemplate() {
		
	}
}

