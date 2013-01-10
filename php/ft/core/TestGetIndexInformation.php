<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	
 * 说明:
 *   各种查看索引状态的办法
 * @author 	can.zhaoc
 *
 */
class TestGetIndexInformation extends PHPUnit_Framework_TestCase {
	
	public static $ch;
	
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		//建索引库
	}
	
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
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
	 http://www.elasticsearch.org/guide/reference/api/admin-indices-stats.html 
	 * _stats提供索引级别的状态统计。可以支持多个索引；可以配置统计选项，支持docs,store,indexing,get,search,warmer,merge,flush,refresh,clear
	 * 比如：curl 'localhost:9200/_stats?merge=true&refresh=true'
	 * 
	 */
	public function testGetStats() {
	}
	
	/**
	 * 说明：
	 http://www.elasticsearch.org/guide/reference/api/admin-indices-status.html 
	 * 和_stats不一样的地方在于，_status提供了更详细的索引状态信息清单
	 *
	 */
	public function testGetStatus() {
	}	
	
	/**
	 * 说明：
	 http://www.elasticsearch.org/guide/reference/api/admin-indices-segments.html
	 * _segments提供了更细粒度的针对每个shard的segment的信息
	 *
	 */	
	public function testGetSegmentInfomation() {
		
	}
	
	/**
	 * http://www.elasticsearch.org/guide/reference/api/admin-indices-indices-exists.html
	 * 通过curl -v -XHEAD 'http://localhost:9200/twitter检查索引是否存在，返回200表示存在，404表示不存在
	 * 因为是head请求，所以response body中是看不出来的，只有在header中看，所以curl命令加个-v显示header的信息
	 * 
	 * 也可以通过同样的方式检查type是否存在，不过要在0.20后的版本才支持
	 * curl -v -XHEAD 'http://localhost:9200/twitter/tweet
	 */
	public function testCheckIndexExist() {
		
	}
}

