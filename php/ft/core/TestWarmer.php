<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	http://www.elasticsearch.org/guide/reference/api/admin-indices-warmers.html
 * 说明:
 *   调用warmer的方式
 * @author 	can.zhaoc
 *
 */
class TestWarmer extends PHPUnit_Framework_TestCase {
	
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
	 * 	索引的warming过程可以让索引在被查询之前就执行一些已经注册的查询请求，以"预热"索引。考虑到搜索的实时性，cold data（segments）
	 * 在实际查询之前将被预热，这个功能自0.20版本后启用
	 * 
	 * warmup查询针对一些大数据量的查询，比如聚类和排序。
	 * 
	 * 索引的warmup过程默认启用，可以通过index.warmer.enabled设为false来关闭，这个配置支持通过实时更新settings的api来完成。
	 * 实时更新配置在批量索引时非常有用，可以在索引前关闭warmer，让索引过程更快、开销更小，索引完成后再重新启用warmer。
	 */
	public function testCreateWarmer() {
	}
}

