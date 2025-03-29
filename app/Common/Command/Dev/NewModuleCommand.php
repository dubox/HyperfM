<?php

declare(strict_types=1);

namespace App\Common\Command\Dev;

use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Utils\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Finder\Finder;

#[Command]
class NewModuleCommand extends HyperfCommand
{
    /**
     * 新建模块
     *  eg：创建一个名为Abc的模块: php bin/hyperf.php dev:m-new Abc
     */
    protected ?string $name = 'dev:m-new';

    public function handle()
    {
        // 从 $input 获取 name 参数
        $name = $this->input->getArgument('name') ?? '';
        if(!$name || !preg_match('/^[A-Z][a-zA-z]*$/',$name)){
            $this->line('模块名必须，且符合大驼峰格式', 'error');
            exit;
        }

        $example_m_name = 'Example';
        $m_path = BASE_PATH . '/app/' . $name;
        $example_m_path = BASE_PATH . '/app/' . $example_m_name;
        
        $fs = new Filesystem();
        $r = $fs->copyDirectory($example_m_path, $m_path);
        if(!$r){
            $this->error('Dir copy fail');
            exit;
        }
        foreach($fs->allFiles($m_path) as $file){
            if(strstr($file->getContents() ,$example_m_name)){
                $r = $fs->put($file->getRealPath(), str_replace($example_m_name,$name,$file->getContents()));
                if($r)
                $this->info($file->getRealPath() . ' ok');
                else
                    $this->error($file->getRealPath() . ' write fail');
            }
            
        }
        $this->info('success');
        
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, '模块名']
        ];
    }





}
