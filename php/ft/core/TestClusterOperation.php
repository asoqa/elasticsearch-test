<?php

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * elasticsearch.org:
 * 	
 * 说明:
 *   操作es集群的接口
 * @author 	can.zhaoc
 *
 */
class TestClusterOperation extends PHPUnit_Framework_TestCase {
	
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
	 http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
	 *通过_cluster/heath对集群的状态进行简单的检查
	 索引健康状态通过green, yellow和red表示，其中：
	 red：表示有shard没有分配
	 yellow: 表示primary shard已经分配，但是副本还没有分配
	 green: 所有shard都已经分配
	 索引的状态由最差的shard状态决定，集群的状态由最差的索引状态决定
	 *curl -XGET 'http://localhost:9200/_cluster/health?pretty=true'
	 *
	 *支持参数有：
	 *level: cluster, indices, shards
	 *wait_for_status: green, yellow, red，默认不等待任何状态
	 *wait_for_relocating_shards: 数值类型，等待多少shards被分配，默认不等待
	 *wait_for_nodes:N, >=N, <=N, >N, <N，等待多少node可用
	 *timeout:
	 */
	public function testCheckClusterHealthyOverall() {
	}
	
	/**
	 * 说明：
	 http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
	 可以指定在一个或多个index上进行检查
	 *curl -XGET 'http://localhost:9200/_cluster/health/test1,test2?pretty=true'
	 *默认情况下，如果索引不存在会检查的比较慢，因为会等待一个超时时间，防止该索引有可能在新建过程中。可以加上timeout参数，
	 *来控制超时时间。
	 *
	 */
	public function testCheckClusterHealthyOnSpecificIndices() {
	}	
	
	
	/**
	 * 说明：
	 http://www.elasticsearch.org/guide/reference/api/admin-cluster-health.html
	 可以指定shard级别上进行检查
	 *$ curl -XGET 'http://localhost:9200/_cluster/health/twitter?level=shards'
	 *
	 */
	public function testCheckClusterHealthyOnShardLevel() {
	}
		
	/**
	 * curl -XGET 'http://localhost:9200/_cluster/health?wait_for_status=yellow&timeout=50s'
	 * 这个功能可以等集群达到一定程度的健康状态，比如等集群在50秒的时间内达到yellow的状态，如果到yellow和green，则返回
	 */
	public function testWaitUntilClusterBecomeHealthy() {
		
	}
	
	/**
	 * 返回整个集群的详细状态
	 * $ curl -XGET 'http://localhost:9200/_cluster/state'
	 * 因为返回内容太多了，可以通过filter过滤出需要关注的部分，支持：
	 * filter_nodes: true/false
	 * filter_routing_table: true/false
	 * filter_metadata: true/false
	 * filter_blocks: true/false
	 * filter_indices: true/false
	 * $ curl -XGET 'http://localhost:9200/_cluster/state?filter_nodes=true'
	 */
	public function testCheckClusterState() {
		
	}
	
	/**
	 * 可以返回所有集群节点的信息
	 curl -XGET 'http://localhost:9200/_cluster/nodes'
	 curl -XGET 'http://localhost:9200/_nodes'
	 	可以返回一个或多个集群节点的信息
	 curl -XGET 'http://localhost:9200/_cluster/nodes/nodeId1,nodeId2'	 	
	 curl -XGET 'http://localhost:9200/_nodes/nodeId1,nodeId2'	 
	 默认情况下，只返回关键的信息。通过将settings, os, process, jvm, thread_pool, network, transport, http
	 设为true可以拿到更多的信息
	 curl -XGET 'http://localhost:9200/_nodes?os=true&process=true'
	 curl -XGET 'http://localhost:9200/_nodes/process'
	 可以通过ip访问节点信息
	 curl -XGET 'http://localhost:9200/_nodes/10.0.0.1/?os=true&process=true'
	 可以通过all拿到所有信息
	 curl -XGET 'http://localhost:9200/_cluster/nodes?all=true'
	 */
	public function testCheckNodesInfo() {
		
	}
	
	/**
	 * 更新集群设置的api，支持两种更新方式：
	 * 1.	persistent ：一直生效
	 * 2.	transient : 重启后无效
	 例如：
	 curl -XPUT localhost:9200/_cluster/settings -d '{
	    "persistent" : {
	        "discovery.zen.minimum_master_nodes" : 2
	    }
	}'
	其他参数详见：http://www.elasticsearch.org/guide/reference/api/admin-cluster-update-settings.html
	通过get方法查阅集群设置：curl -XGET localhost:9200/_cluster/settings
	 */
	public function testUpdateClusterSetting() {
		
	}
	
	/**
	 * 获取nodes节点的统计信息
	 * curl -XGET 'http://localhost:9200/_cluster/nodes/stats'
	 * 具体用法同上
	 */
	public function testGetNodesStat() {
		
	}
	
	/**
	 * 关闭所有集群节点
	 * $ curl -XPOST 'http://localhost:9200/_cluster/nodes/_local/_shutdown'
	 * 关闭指定节点
	 * $ curl -XPOST 'http://localhost:9200/_cluster/nodes/nodeId1,nodeId2/_shutdown'
	 * 关闭master
	 * $ curl -XPOST 'http://localhost:9200/_cluster/nodes/_master/_shutdown'
	        通过discovery.zen.minimum_master_nodes控制自动选举master的最小节点数，通过update setting的api做，光改elasticsearch.yml是没有用的。
	 * 可以延迟关闭
	 * $ curl -XPOST 'http://localhost:9200/_cluster/nodes/_local/_shutdown?delay=10s'
	 * 通过action.disable_shutdown禁用shutdown
	 */
	public function testShutdownNodes() {
		
	}
	
	/**
	 * 获取集群每个节点当前的hot threads信息。通过/_nodes/hot_threads，和/_nodes/{nodesIds}/hot_threads
	 * 这个api是试验性质的
	 */
	public function testGetHotThreadsInfo() {
		
	}
	
	/**
	 * 重新路由的功能可以实现把一个shard从一个节点移动到另一个节点，取消节点分配shard，或者在指定节点上分片shard：
	 * curl -XPOST 'localhost:9200/_cluster/reroute' -d '{
		    "commands" : [ {
		        "move" : 
		            {
		              "index" : "test", "shard" : 0, 
		              "from_node" : "node1", "to_node" : "node2"
		            }
		        },
		        {
		          "allocate" : {
		              "index" : "test", "shard" : 1, "node" : "node3"
		          }
		        }
		    ]
		}'
		command支持的命令有move, cancel, allocate
		
		需要注意的是一旦分片发生后，集群会重新进行负载平衡（re-balancing）。例如，如果把一个分片从node1移动到node2，在负载均衡
		策略下，另一个分片将会从node2移动到node1.
		
		可以设置禁止自动分配，这意味着只能够进行明确的分配操作。如果所有命令应用之后，集群将进行负载均衡。
		
		或者可以在dry_run模式下执行这些命令
	 */
	public function testReroute() {
		
	}
}

