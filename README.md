## 简介
基于 Hyperf3.0 + php8.1 改造的多模块支持项目；
每个模块也可以轻松独立部署，互不影响；

## 功能 
除了多模块支持外，还增加了以下功能/优化：

- 权限校验注解：Common\Annotation\AuthCheck
- 事件总线：Common\Event\EventBus
- 异步监听器：Common\Constants\AsyncListenerInterface
- 消息模板：Common\Message\\*
- 表单验证器优化
- 其他...

## 使用
- 运行
```bash
# 构建镜像
docker build -t HyperfM . 
# 启动容器 后台运行
docker run -d --name hm -v $(pwd):/opt/www -p 9601:9501 HyperfM
# or 进入命令行
docker run -it --name hm -v $(pwd):/opt/www -p 9601:9501 HyperfM bash
```
- 创建模块
```bash
#创建一个名为Abc的模块: 
php bin/hyperf.php dev:m-new Abc
```


## Hyperf 启动流程
![Hyperf 启动流程图](https://app-res.thisnet.cn/thisnet/upload2025-03-29T20:37:47.png)