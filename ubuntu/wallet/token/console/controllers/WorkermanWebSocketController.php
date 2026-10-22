<?php

namespace console\controllers;

use Workerman\Worker;
use yii\helpers\Console;
use yii\console\Controller;
use Workerman\Protocols\Websocket;

class WorkermanWebSocketController extends Controller
{
    public $send;
    public $daemon;
    public $gracefully;

    // 这里不需要设置，会读取配置文件中的配置
    public $config = [];
    private $ip = '0.0.0.0';
    private $port = '8080';

    public function options($actionID)
    {
        return ['send', 'daemon', 'gracefully'];
    }

    public function optionAliases()
    {
        return [
            's' => 'send',
            'd' => 'daemon',
            'g' => 'gracefully',
        ];
    }

    public function actionIndex()
    {
        if ('start' == $this->send) {
            try {
                $this->start($this->daemon);
            } catch (\Exception $e) {
                $this->stderr($e->getMessage() . "\n", Console::FG_RED);
            }
        } else if ('stop' == $this->send) {
            $this->stop();
        } else if ('restart' == $this->send) {
            $this->restart();
        } else if ('reload' == $this->send) {
            $this->reload();
        } else if ('status' == $this->send) {
            $this->status();
        } else if ('connections' == $this->send) {
            $this->connections();
        }
    }

    public function initWorker()
    {
        $ip = isset($this->config['ip']) ? $this->config['ip'] : $this->ip;
        $port = isset($this->config['port']) ? $this->config['port'] : $this->port;

        define('HEARTBEAT_TIME', 5);
        $wsWorker = new Worker("websocket://{$ip}:{$port}");

        // 4 processes
        $wsWorker->count = 4;
        $wsWorker->uidConnections = [];
        global $uids;
        // Emitted when new connection come
        $wsWorker->onConnect = function ($connection) {
            echo "New connection\n";
        };

//        $wsWorker->onConnect = function($connection) {
//            // 给链接对象临时赋值一个lastTime属性记录上次接收消息的时间
//            $connection->lastTime = time();
//        };


        // 进程启动后设置一个每秒运行一次的定时器
        $wsWorker->onWorkerStart = function ($worker) {
            \Workerman\Lib\Timer::add(1, function () use ($worker) {
                $time_now = time();
                foreach ($worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > HEARTBEAT_TIME) {
                        $connection->close();
                    }
                }
            });
        };

        // Emitted when data received
        $wsWorker->onMessage = function ($connection, $data) {
            if ('binance' == $data) {
                $con = new \Workerman\Connection\AsyncTcpConnection("ws://stream.binance.com:9443/ws/!ticker@arr");
                $con->transport = 'ssl';
                $con->onMessage = function ($con, $data) use ($connection) {
                    $base_coin = [
                        'ETH', 'BTC', 'USDT', 'BTY'
                    ];
                    $result = json_decode($data, true);
                    $ticker = [];
                    foreach ($result as $val) {
                        foreach ($base_coin as $k => $coin) {
                            $explode_arr = explode($coin, $val['s']);
                            if (2 == count($explode_arr) && empty($explode_arr[1])) {
                                $temp = [];
                                $temp['symbol'] = $explode_arr[0] . '/' . $coin;
                                $temp['close'] = (float)sprintf("%0.4f", $val['c']);
                                $temp['change'] = (float)sprintf("%0.4f", $val['p'] * 100);
                                $temp['high'] = (float)sprintf("%0.4f", $val['h']);
                                $temp['low'] = (float)sprintf("%0.4f", $val['l']);
                                $temp['vol'] = (float)sprintf("%0.4f", $val['v']);
                                array_push($ticker, $temp);
                                break;
                            }
                        }
                    }
                    $connection->send('binance : ' . json_encode($ticker));
                };
                $con->connect();
            } elseif ('huobi' == $data) {
                //$connection->websocketType = Websocket::BINARY_TYPE_ARRAYBUFFER;
                // ssl需要访问443端口
                $con = new \Workerman\Connection\AsyncTcpConnection('ws://api.huobi.pro:443/ws');
                // 设置以ssl加密方式访问，使之成为wss
                $con->transport = 'ssl';
                $con->onConnect = function ($con) {
                    $data = json_encode([
                        #'sub' => 'market.btcusdt.kline.1min',
                        #'id' => 'depth' . time(),
                        'sub' => 'market.overview'
                    ]);
                    $con->send($data);
                };
                $con->onMessage = function ($con, $data) use ($connection) {
                    $data = gzdecode($data);
                    $data = json_decode($data, true);
                    if (isset($data['ping'])) {
                        $con->send(json_encode([
                            "pong" => $data['ping']
                        ]));
                    } else if (isset($data['ch']) && 'market.overview' == $data['ch']) {
                        $base_coin = [
                            'ETH', 'BTC', 'USDT', 'BTY'
                        ];
                        $ticker = [];
                        foreach ($data['data'] as $val) {
                            foreach ($base_coin as $k => $coin) {
                                $explode_arr = explode($coin, strtoupper($val['symbol']));
                                if (2 == count($explode_arr) && empty($explode_arr[1])) {
                                    $temp = [];
                                    $temp['symbol'] = $explode_arr[0] . '/' . $coin;
                                    $temp['close'] = (float)sprintf("%0.4f", $val['close']);
                                    $temp['change'] = (0 == $val['open']) ? 0 : (float)sprintf("%0.4f", ($val['close'] - $val['open']) / $val['open'] * 100);
                                    $temp['high'] = (float)sprintf("%0.4f", $val['high']);
                                    $temp['low'] = (float)sprintf("%0.4f", $val['low']);
                                    $temp['vol'] = (float)sprintf("%0.4f", $val['vol']);
                                    array_push($ticker, $temp);
                                    break;
                                }
                            }
                        }
                        $connection->send('huobi : ' . json_encode($ticker));
                    } else {
                    }
                };
                $con->connect();
            } elseif ('okex' == $data) {
                $connection->websocketType = Websocket::BINARY_TYPE_ARRAYBUFFER;
                $con = new \Workerman\Connection\AsyncTcpConnection("ws://real.okex.com:8443/ws/v3");
                $con->transport = 'ssl';
                $con->onConnect = function ($con) {
                    $data = json_encode([
                        'op' => 'subscribe',
                        'args' => [
                            'spot/all_ticker_3s',
                            'futures/all_ticker_3s',
                            'index/all_ticker_3s',
                            'futures/delivery:BTC'
                        ]
                    ]);
                    $con->send("{\"op\":\"subscribe\",\"args\":[\"spot/all_ticker_3s\",\"futures/all_ticker_3s\",\"index/all_ticker_3s\",\"futures/delivery:BTC\"]}");
                };
                $con->onMessage = function ($con, $data) use ($connection) {
                    $data = gzdecode($data);
                    $connection->send(date("Y-m-d H:i:s") . ' : ' . json_encode($data));
                };
                $con->connect();
            } else {
                $connection->send('other');
            }
        };

        // Emitted when connection closed
        $wsWorker->onClose = function ($connection) {
            echo "Connection closed\n";
        };
    }

    /**
     * workman websocket start
     */
    public function start()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'start';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket restart
     */
    public function restart()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'restart';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket stop
     */
    public function stop()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'stop';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket reload
     */
    public function reload()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'reload';
        if ($this->gracefully) {
            $argv[2] = '-g';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket status
     */
    public function status()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'status';
        if ($this->daemon) {
            $argv[2] = '-d';
        }

        // Run worker
        Worker::runAll();
    }

    /**
     * workman websocket connections
     */
    public function connections()
    {
        $this->initWorker();
        // 重置参数以匹配Worker
        global $argv;
        $argv[0] = $argv[1];
        $argv[1] = 'connections';

        // Run worker
        Worker::runAll();
    }
}