<?php

declare(strict_types=1);

namespace App\Common\Command\Crontab;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

/**
 * 定时任务脚本示例
 */
#[Command]
class TestCommand extends HyperfCommand
{

    /**
     * 执行的命令行
     * eg：php bin/hyperf.php crontab:test 111 222
     */
    protected ?string $name = 'crontab:test';

    /**
     * 设置参数
     * InputArgument::REQUIRED 参数必填，此种模式 default 字段无效
     * InputArgument::OPTIONAL 参数可选，常配合 default 使用
     * InputArgument::IS_ARRAY 数组类型
     */
    public function configure()
    {
        parent::configure();
        $this->addArgument('param1', InputArgument::OPTIONAL, '参数1', '111');
        $this->addArgument('param2', InputArgument::OPTIONAL, '参数2', '222');
    }

    public function handle()
    {
        // 从$input获取参数
        $param1 = $this->input->getArgument('param1');

        // 通过内置方法line在Console输出，输出内容会被任务管理工具记录
        $this->line($param1, 'info');

        $this->line($this->input->getArgument('param2'), 'info');

        if ($param1 != '111') {
            $this->error('fail');
            exit;
        }

        $this->info('success');
    }
}
