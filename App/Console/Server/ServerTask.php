<?php
namespace App\Console\Server;

use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use App\Console\Base;
use PhpSchool\CliMenu\MenuStyle;
use Swoole\Async;

class ServerTask extends Base
{

    private $serv;

    public function initialize()
    {

    }

    public function mainAction()
    {
        $this->serv = $server = new ServicesTask();
        $menu       = (new CliMenuBuilder)
            ->setTitle('EasyPhalcon Cli Menu')
            ->addSubMenu('Start', function (CliMenuBuilder $b) use ($server)
            {
                $b->setTitle('EasyPhaclon Cli Menu >> Start')
                  ->addItem('常驻服务方式启动', function (CliMenu $menu) use ($server)
                  {
                      $is_daemon = true;
                      self::startService($is_daemon);
                      usleep(500000);
                      $pid = $server->getPid();
                      if (is_numeric($pid)) {
                          echo '进程正常启动服务ID为' . $pid . PHP_EOL;
                      } else {
                          echo '进程启动失败原因为:' . $pid . PHP_EOL;
                      }
                  })->addItem('后台服务方式启动', function (CliMenu $menu) use ($server)
                    {
                        $is_daemon = false;
                        self::startService($is_daemon);
                        usleep(500000);
                        $pid = $server->getPid();
                        if (is_numeric($pid)) {
                            echo '进程正常启动服务ID为' . $pid . PHP_EOL;
                        } else {
                            echo '进程启动失败原因为:' . $pid . PHP_EOL;
                        }
                        /**
                         * 当不是守护进程的时候，启动一个进程监听主程序信号
                         */
                        if (!$is_daemon) {
                            \swoole_process::signal(SIGINT, function ($signo) use ($pid, $server)
                            {
                                $pid = $server->getPid();
                                if (is_numeric($pid)) {
                                    if (shell_exec("ps -ef |grep $pid|grep -v \"grep\"| awk '{print $2}'")) {
                                        shell_exec("kill -TERM $pid");
                                    }
                                }
                                $server->clearPidAll();
                                exit(0);
                            });
                        }
                    });
            })
            ->addItem('Stop', function () use ($server)
            {
                $pid = $server->getPid();
                if (is_numeric($pid)) {
                    if (!shell_exec("ps -ef |grep $pid|grep -v \"grep\"| awk '{print $2}'")) {
                        echo "服务未正常运行" . PHP_EOL;
                    } else {
                        shell_exec("kill -TERM $pid");
                        echo "服务进程PID:$pid 已停止" . PHP_EOL;
                    }
                } else {
                    echo '服务未正常运行' . PHP_EOL;
                }
                $server->clearPidAll();
            })
            ->addItem('Status', function () use ($server)
            {
                $pid = $server->getPid();
                if (is_numeric($pid)) {
                    if ($out = shell_exec('pstree -p ' . $pid)) {
                        echo "服务信息查看成功:进程信息为:$out" . PHP_EOL;
                    } else {
                        echo "服务未正常运行";
                    }
                } else {
                    echo "服务未正常运行";
                }
            })
            ->addItem('Reload', function () use ($server)
            {

            })
            ->setBackgroundColour('237')
            ->setForegroundColour('214')
            ->setBorder(0, 0, 0, 2, '165')
            ->setPaddingTopBottom(4)
            ->setPaddingLeftRight(8)
            ->addLineBreak('-')
            ->setMarginAuto()
            ->build();
        $menu->open();
    }

    /**
     * @param bool $is_daemon
     */
    public static function startService(bool $is_daemon)
    {
        if ($is_daemon) {
            $cmd = 'php SwooleServer.php Services start';
            echo shell_exec($cmd);
        } else {
            Async::exec('php SwooleServer.php Services start', function ($res, $status)
            {
                echo "返回结果:" . PHP_EOL;
                var_dump($res);
                echo "返回信号:" . PHP_EOL;
                var_dump($status);
            });
        }
    }

    public function menuAction()
    {
        $itemCallable = function (CliMenu $menu)
        {
            $flash = $menu->flash("PHP School FTW!!");
            $flash->getStyle()->setBg('green');
            $flash->display();
        };
        $menu         = (new CliMenuBuilder)
            ->setTitle('Basic CLI Menu')
            ->addItem('First Item', $itemCallable)
            ->addItem('Second Item', $itemCallable)
            ->addItem('Third Item', $itemCallable)
            ->addLineBreak('-')
            ->build();
        $menu->open();
        //        $itemCallable = function (CliMenu $menu) {
        //            $menu->confirm('PHP School FTW!')
        //                 ->display('OK');
        //        };
        //        $menu = (new CliMenuBuilder)
        //            ->setTitle('Basic CLI Menu')
        //            ->addItem('First Item', $itemCallable)
        //            ->addItem('Second Item', $itemCallable)
        //            ->addItem('Third Item', $itemCallable)
        //            ->addLineBreak('-')
        //            ->build();
        //        $menu->open();
        //        $itemCallable = function (CliMenu $menu)
        //        {
        //            echo $menu->getSelectedItem()->getText();
        //        };
        //        $menu         = (new CliMenuBuilder)
        //            ->setTitle('CLI Menu')
        //            ->addItem('First Item', $itemCallable)
        //            ->addItem('Second Item', $itemCallable)
        //            ->addLineBreak('-')
        //            ->addSubMenu('Options', function (CliMenuBuilder $b)
        //            {
        //                $b->setTitle('CLI Menu > Options')
        //                  ->addItem('First option', function (CliMenu $menu)
        //                  {
        //                      echo sprintf('Executing option: %s', $menu->getSelectedItem()->getText());
        //                  })
        //                  ->addLineBreak('-');
        //            })
        //            ->setWidth(70)
        //            ->setBackgroundColour('blue')
        //            ->build();
        //        $menu->open();
    }

}