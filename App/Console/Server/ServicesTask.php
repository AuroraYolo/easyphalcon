<?php
namespace App\Console\Server;

use App\Commons\Library\Logger;
use App\Console\Base;
use Swoole\Async;
use Swoole\Server;

class ServicesTask extends Base
{

    /**
     * @var  \Swoole\Server
     */
    protected $_serv = null;
    /**
     * @var string
     */
    protected $connectionCacheKey = 'tcp';
    /**
     * @var Event
     */
    protected $_event = null;
    /**
     * @var integer $masterPidKey
     */
    protected static $masterPidKey = 'TCP_MASTER_PID';
    /**
     * @var int
     */
    protected $masterPid = 0;
    /**
     * @var \Phalcon\Logger\Adapter\File;
     */
    private $logger;

    /**
     * @throws \Exception
     */
    public function initialize()
    {
        $this->_event = new Event();
        $this->logger = Logger::logger('swoole');
    }

    public function startAction()
    {
        $this->_serv = $server = new Server('0.0.0.0', 9530, SWOOLE_PROCESS, SWOOLE_SOCK_TCP);
        $this->_serv->set([
            'reactor_num'     => 2,//通过此参数来调节主进程内事件处理线程的数量，以充分利用多核。默认会启用CPU核数相同的数量。一般设置为CPU核数的1-4倍
            'worker_num'      => 4, //设置启动的Worker进程数 业务代码是全异步非阻塞的，这里设置为CPU的1-4倍最合理
            'max_conn'        => 4864,//最大允许的连接数，如max_connection => 10000, 此参数用来设置_serv最大允许维持多少个TCP连接。超过此数量后，新进入的连接将被拒绝
            'task_worker_num' => 4,//配置Task进程的数量，配置此参数后将会启用task功能。所以_serv务必要注册onTask、onFinish2个事件回调函数。如果没有注册，服务器程序将无法启动。
            'dispatch_mode'   => 2, //数据包分发策略  默认为2 固定模式
            'daemonize'       => true
        ]);
        $this->_serv->on('Start', [
            $this,
            'onStart'
        ]);
        $this->_serv->on('ManagerStart', [
            $this,
            'onManagerStart'
        ]);
        $this->_serv->on('ManagerStop', [
            $this,
            'onManagerStop'
        ]);
        $this->_serv->on('WorkerStart', [
            $this,
            'onWorkerStart'
        ]);
        $this->_serv->on('WorkerStop', [
            $this,
            'onWorkerStop'
        ]);
        $this->_serv->on('Connect', [
            $this,
            'onConnect'
        ]);
        $this->_serv->on('Receive', [
            $this,
            'onReceive'
        ]);
        $this->_serv->on('Close', [
            $this,
            'onClose'
        ]);
        $this->_serv->on('Task', [
            $this,
            'onTask'
        ]);
        $this->_serv->on('Finish', [
            $this,
            'onFinish'
        ]);
        $this->_serv->on('Shutdown', [
            $this,
            'onShutdown'
        ]);
        $this->_serv->start();
    }

    /**
     * Server启动在主进程的主线程回调此函数
     * @deprecated 在此事件之前Swoole Server已进行了如下操作
     *已创建了manager进程
     *已创建了worker子进程
     *已监听所有TCP/UDP/UnixSocket端口，但未开始Accept连接和请求
     *已监听了定时器
     *接下来要执行
     *主Reactor开始接收事件，客户端可以connect到Server
     *onStart回调中，仅允许echo、打印Log、修改进程名称。不得执行其他操作。onWorkerStart和onStart回调是在不同进程中并行执行的，不存在先后顺序。
     *可以在onStart回调中，将$serv->master_pid和$serv->manager_pid的值保存到一个文件中。这样可以编写脚本，向这两个PID发送信号来实现关闭和重启的操作。
     *从1.7.5+ Master进程内不再支持定时器，onMasterConnect/onMasterClose2个事件回调也彻底移除。Master进程内不再保留任何PHP的接口。
     *onStart事件在Master进程的主线程中被调用。
     *
     * @param Server $server
     */

    public function onStart(Server $server)
    {
        // $this->setProcessName('SwooleMaster');
        $this->masterPid = $server->master_pid;
        try {
            $this->listenTcpStart();
        } catch (\Exception $ex) {
            $this->logger->warning("<error>" . $ex->getMessage() . "</error>");
            Async::exec("kill -TERM $this->masterPid", function ($res, $status)
            {
            });
        }
    }

    /**
     * @throws \Exception
     */
    private function listenTcpStart()
    {
        $this->redis();
        $this->redis()->hSet(self::$masterPidKey, 'server', $this->masterPid);
        $this->redis()->del($this->connectionCacheKey);
        $this->redis()->close();
    }

    /**
     * @param Server $server
     *
     *在此之前Swoole\Server已进行了如下操作
     *已关闭所有Reactor线程、HeartbeatCheck线程、UdpRecv线程
     *已关闭所有Worker进程、Task进程、User进程
     *已close所有TCP/UDP/UnixSocket监听端口
     *已关闭主Reactor
     *
     * @throws \Exception
     */
    public function onShutdown(Server $server)
    {
        $this->redis()->hDel($this->masterPidKey, 'server');
    }

    /**
     * 在这个回调函数中可以修改管理进程的名称。
     *
     * @param Server $server
     */
    public function onManagerStart(Server $server)
    {
        echo __METHOD__;
    }

    /**
     * @param Server $server
     */
    public function onManagerStop(Server $server)
    {
        echo __METHOD__;
    }

    /**
     * 此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用。原型：
     *
     * function onWorkerStart(swoole_server $server, int $worker_id);
     * 1.6.11之后Task进程中也会触发onWorkerStart事件
     * 发生致命错误或者代码中主动调用exit时，Worker/Task进程会退出，管理进程会重新创建新的进程
     * onWorkerStart/onStart是并发执行的，没有先后顺序
     * 可以通过$server->taskworker属性来判断当前是Worker进程还是Task进程
     * 如果想使用Reload机制实现代码重载入，必须在onWorkerStart中require你的业务文件，而不是在文件头部。在onWorkerStart调用之前已包含的文件，不会重新载入代码。
     *
     * 可以将公用的、不易变的php文件放置到onWorkerStart之前。这样虽然不能重载入代码，但所有Worker是共享的，不需要额外的内存来保存这些数据。
     * onWorkerStart之后的代码每个进程都需要在内存中保存一份
     *
     * $worker_id是一个从0-$worker_num之间的数字，表示这个Worker进程的ID
     * $worker_id和进程PID没有任何关系，可使用posix_getpid函数获取PID
     *
     * @param Server $server
     * @param int    $workerId
     */
    public function onWorkerStart(Server $server, int $workerId)
    {
        echo __METHOD__;
    }

    /**
     * $worker_id是一个从0-$worker_num之间的数字，表示这个worker进程的ID
     * $worker_id和进程PID没有任何关系
     * 进程异常结束，如被强制kill、致命错误、core dump 时无法执行onWorkerStop回调函数
     *
     * @param Server $server
     * @param int    $workerId
     */
    public function onWorkerStop(Server $server, int $workerId)
    {
        echo __METHOD__;
    }

    /**
     *
     * $server是Swoole\Server对象
     * $fd是连接的文件描述符，发送数据/关闭连接时需要此参数
     * $reactorId来自哪个Reactor线程
     * 关于$fd和$reactorId 详细的解释
     * onConnect/onClose这2个回调发生在worker进程内，而不是主进程。
     * UDP协议下只有onReceive事件，没有onConnect/onClose事件
     *
     * @param Server $server
     * @param int    $fd
     * @param int    $reactorId
     */
    public function onConnect(Server $server, int $fd, int $reactorId)
    {
        //存储客户端用户信息
        $clientInfo = $server->connection_info($fd);
        $this->logger->info(sprintf('客户端IP:[%s],客户端端口:[%s],连接时间为:[%s]', $clientInfo['remote_ip'], $clientInfo['remote_port'], date('Y-m-d H:i:s', $clientInfo['connect_time'])));
    }

    /**
     *
     * $server，swoole_server对象
     * $fd，TCP客户端连接的唯一标识符
     * $reactor_id，TCP连接所在的Reactor线程ID
     * $data，收到的数据内容，可能是文本或者二进制内容
     * 关于$fd和$reactor_id 详细的解释
     * 未开启swoole的自动协议选项，onReceive回调函数单次收到的数据最大为64K
     * Swoole支持二进制格式，$data可能是二进制数据
     *
     * 协议相关说明
     * UDP协议，onReceive可以保证总是收到一个完整的包，最大长度不超过64K
     * UDP协议下，$fd参数是对应客户端的IP，$reactor_id是客户端的端口和来源端口； 客户端ip等于long2ip(unpack('N',pack('L',$fd))[1])；
     * TCP协议是流式的，onReceive无法保证数据包的完整性，可能会同时收到多个请求包，也可能只收到一个请求包的一部分数据
     * swoole只负责底层通信，$data是通过网络接收到的原始数据。对数据解包打包需要在PHP代码中自行实现
     * 如果开启了eof_check/length_check/http_protocol，$data的长度可能会超过64K，但最大不超过$server->setting['package_max_length']
     *
     * 注意，onReceive回调不再支持UDP Server
     *
     * @param Server $server
     * @param int    $fd
     * @param int    $reactorId
     */
    public function onReceive(Server $server, int $fd, int $reactorId)
    {
        $params = [
            'fd'  => $fd,
            'src' => $reactorId
        ];
        $server->task($params);
    }

    /**
     *
     * $server 是swoole_server对象
     * $fd 是连接的文件描述符
     * $reactorId 来自那个reactor线程
     * onClose回调函数如果发生了致命错误，会导致连接泄漏。通过netstat命令会看到大量CLOSE_WAIT状态的TCP连接
     * 无论由客户端发起close还是服务器端主动调用$serv->close()关闭连接，都会触发此事件。因此只要连接关闭，就一定会回调此函数
     * 1.7.7+版本以后onClose中依然可以调用connection_info方法获取到连接信息，在onClose回调函数执行完毕后才会调用close关闭TCP连接
     *
     * 注意：这里回调onClose时表示客户端连接已经关闭，所以无需执行$server->close($fd)。代码中执行$serv->close($fd)会抛出PHP错误告警
     *
     * @param Server $server
     * @param int    $fd
     * @param int    $reactorId
     */
    public function onClose(Server $server, int $fd, int $reactorId)
    {
        try {
            if ($this->redis()->exists($this->connectionCacheKey)) {
                $this->redis()->zRem($this->connectionCacheKey, $fd);
            }
        } catch (\Exception $ex) {
        }
    }

    /**
     * 在task_worker进程内被调用。worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务。当前的Task进程在调用onTask回调函数时会将进程状态切换为忙碌，这时将不再接收新的Task，当onTask函数返回时会将进程状态切换为空闲然后继续接收新的Task。
     *
     * function onTask(swoole_server $serv, int $task_id, int $src_worker_id, mixed $data);
     * $task_id是任务ID，由swoole扩展内自动生成，用于区分不同的任务。$task_id和$src_worker_id组合起来才是全局唯一的，不同的worker进程投递的任务ID可能会有相同
     * $src_worker_id来自于哪个worker进程
     * $data 是任务的内容
     * onTask函数执行时遇到致命错误退出，或者被外部进程强制kill，当前的任务会被丢弃，但不会影响其他正在排队的Task
     *
     * @param Server $server
     * @param int    $taskId
     * @param int    $srcWorkerId
     * @param mixed  $data
     */
    public function onTask(Server $server, int $taskId, int $srcWorkerId, mixed $data)
    {
        var_dump($data);
        $server->finish();
    }

    /**
     * $task_id是任务的ID
     * $data是任务处理的结果内容
     * task进程的onTask事件中没有调用finish方法或者return结果，worker进程不会触发onFinish
     *
     * 执行onFinish逻辑的worker进程与下发task任务的worker进程是同一个进程
     *
     * @param Server $server
     * @param int    $taskId
     * @param int    $srcWorkerId
     * @param string $data
     */
    public function onFinish(Server $server, int $taskId, int $srcWorkerId, string $data)
    {

    }

    /**
     * 设置主进程名称
     *
     * @param string $process_name
     *
     * @return bool
     */
    private function setProcessName($process_name = 'SwooleMaster')
    {
        if (!swoole_set_process_name($process_name)) {
            return false;
        }
        return true;
    }

    /**
     * 获取服务的进程ID
     *
     * @return string
     */
    public function getPid()
    {
        try {
            if ($this->redis()->exists(self::$masterPidKey)) {
                if ($this->redis()->hExists(self::$masterPidKey, 'server')) {
                    $pid = $this->redis()->hGet(self::$masterPidKey, 'server');
                    return $pid;
                } else {
                    return '服务未正常启动';
                }
            } else {
                return '服务未正常启动';
            }
        } catch (\Exception $ex) {
            $ex->getMessage();
        }
    }

    /**
     * 清除所有服务的进程ID
     */
    public function clearPidAll()
    {
        try {
            $this->redis()->del(self::$masterPidKey);
        } catch (\Exception $ex) {

        }
    }

}