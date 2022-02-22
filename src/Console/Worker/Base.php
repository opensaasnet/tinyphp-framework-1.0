<?php
/**
 *
 * @copyright (C), 2013-, King.
 * @name Base.php
 * @author King
 * @version Beta 1.0
 * @Date 2020年4月6日上午11:00:39
 * @Description
 * @Class List 1.
 * @Function List 1.
 * @History King 2020年4月6日上午11:00:39 第一次建立该文件
 *          King 2020年4月6日上午11:00:39 修改
 *          King 2020年6月1日14:21 stable 1.0.01 审定
 *
 */
namespace Tiny\Console\Worker;

/**
 * Worker基类
 *
 * @package Tiny.Console.Worker
 * @since 2020年6月1日下午2:25:05
 * @final 2020年6月1日下午2:25:05
 */
abstract class Base
{

    /**
     * worker进程ID
     *
     * @var integer
     */
    protected $_pid = 0;

    /**
     * workerID
     *
     * @var integer
     */
    protected $_id;
    
    /**
     * daemon pid
     * @var int
     */
    protected $_daemonPid;
    
    /**
     * Daemon pid file
     * @var string
     */
    protected $_daemonPidFile;

    /**
     * 默认开启的工作进程
     *
     * @var integer
     */
    protected $_num = 1;

    /**
     * 执行worker委托的代理实例
     *
     * @var IWorkerHandler
     */
    protected $_handler;

    /**
     * 策略数组
     *
     * @var array
     */
    protected $_options = [];

    /**
     * 参数
     * @var array
     */
    protected $_args = [];

    /**
     * 执行worker委托的控制器名称
     * @var string
     */
    protected $_controller;
    
    /**
     * 执行worker委托的回调函数
     *
     * @var callable
     */
    protected $_action;
    
    /**
     * 构造函数
     */
    public function __construct(array $config = [])
    {
        $this->_pid = posix_getpid();
        $ret = $this->_formatConfig($config);
        if(!$ret)
        {
            throw new WorkerException(sprintf('Worker Excetion: options：%s is format faild!', var_export($config, TRUE)));
        }
    }

    /**
     * 获取worker的ID 进程间通讯
     *
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * 获取worker的进程数
     *
     * @return int
     */
    public function getNum()
    {
        return $this->_num;
    }

    /**
     * 设置worker回调的handler实例
     *
     * @param IWorkerHandler $whandler
     */
    public function setWorkerHandler(IWorkerHandler $whandler)
    {
        $this->_handler = $whandler;
    }

    /**
     * 获取设置的worker回调的handler实例
     *
     * @return mixed
     */
    public function getWorkerHandler()
    {
        return $this->_handler;
    }

    /**
     * 设置守护进程的选型
     * @param int $daemonPid
     * @param string $daemonPidFile
     */
    public function setDaemonOptions($daemonPid, $daemonPidFile)
    {
        $this->_daemonPid = $daemonPid;
        $this->_daemonPidFile = $daemonPidFile;
    }
    
    /**
     * 初始化 在成为子进程之前
     *
     * @return boolean
     */
    public function init()
    {
        return TRUE;
    }

    /**
     * 成为子进程后 开始
     *
     * @return boolean
     */
    public function start()
    {
        // init pid
        $this->_pid = posix_getpid();
	return $this->onstart();
    }

    /**
     * exit 退出后
     *
     * @return boolean
     */
    public function stop()
    {
	return $this->onstop();
    }
    
    /**
     * 判断主进程是否仍在正常运行
     */
    public function  daemonIsRunning()
    {
        return file_exists($this->_pidfile);
    }
    
    /**
     * 调用handler的函数
     *
     * @param string $method
     * @param array $args
     * @return boolean|mixed
     */
    public function __call($method, $args)
    {
        if (!$this->_handler)
        {
            return NULL;
        }
        
        $isEvent = FALSE;
        if (substr($method, 0, 2) == 'on')
        {
            $isEvent = TRUE;
        }
        // 参数
        $controller = $this->_controller;
        $args = is_array($args) ? $args : [];

        // callback
        $callback = [
            $this->_handler,
            'onWorkerDispatch'
        ];

        // param array
        $params = [
            $controller,
            $method,
            $args,
            $isEvent
        ];

        // call callback
        $ret = call_user_func_array($callback, $params);
        return $ret;
    }

    /**
     * 守护进程是否正常运行
     * 
     * @return boolean|boolean|void
     */
    protected function _daemonIsRunning()
    {
        if (!file_exists($this->_daemonPidFile))
        {
            return FALSE;
        }
        $pid = (int)file_get_contents($this->_daemonPidFile);
        if ($pid != $this->_daemonPid)
        {
            return FALSE;
        }
        $pidIsExists = file_exists('/proc/' . $pid);
        return $pidIsExists;
    }
    
    /**
     * worker正式运行
     */
    abstract public function run();

    /**
     * 格式化选项数组
     *
     * @param array $options
     * @throws WorkerException
     */
    protected function _formatConfig(array $config)
    {
        if (!$config['id'])
        {
            return FALSE;
        }
        $this->_id = $config['id'];

        // hanlder onworkerevent args
        if (is_array($config['args']))
        {
            $this->_args = array_merge($this->_args, $config['args']);
        }

        //附带选型
        if (is_array($config['options']))
        {
            $this->_options = array_merge($this->_options, $config['options']);
        }
        
        // handler
        if ($config['handler'] && $config['handler'] instanceof IWorkerHandler)
        {
            $this->_handler = $config['handler'];
        }

        // worker num
        if (isset($config['num']) && $config['num'] > 0)
        {
            $this->_num = (int)$config['num'];
        }
        $this->_controller = $this->_args['controller'] ?: 'main';
        $this->_action = $this->_args['action'] ?: 'index';
        return TRUE;
    }
}
?>
