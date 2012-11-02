<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * test case.
 */
class TestForTest extends PHPUnit_Framework_TestCase {
	
	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp() {
		parent::setUp ();
		
		// TODO Auto-generated TestForTest::setUp()
	}
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated TestForTest::tearDown()
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	public function testIndex() {
		$ch = curl_init();
		
		$method = "POST";
		$id = 1;
		$url = "http://10.232.42.205:9200/test/index/";
		
		$index = '{
		    "message" : "trying out Elastic Search"
		}';
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $index);
		curl_setopt($ch, CURLOPT_POST, 1 );
		curl_setopt($ch, CURLOPT_HEADER, 0 );
		
		$result = curl_exec($ch);
		
		curl_close($ch);
	}
}

