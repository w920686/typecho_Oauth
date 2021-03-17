# typecho_Oauth
typecho免申请第三方应用集成多种第三方登录插件

![演示图](https://www.gmit.vip/usr/uploads/2020/12/783730042.png)

# 演示站
https://muyu.mobi/

https://www.gmit.vip/


# 使用方法
----
下载之后把插件丢到 `plugins` 目录 目录名改成 `GmOauth`
后台启动之后以下代码放在要输出登录按钮位置


    <?php GmOauth_Plugin::GmOauth(); ?>

即可
登录按钮基于 handsome 模板
其他模板按钮排版需要加以下css

    .btn-icon.btn-sm {
        width: 30px;
        height: 30px;
    }

    a.btn {
        border: none!important;
    }
    .bg-white a {
        color: inherit;
    }
    .btn-rounded {
        padding-right: 15px;
        padding-left: 15px;
        border-radius: 50px;
    }
    .btn-icon {
        width: 34px;
        height: 34px;
        padding: 0!important;
        text-align: center;
    }
    .btn-default {
        color: #58666e!important;
        background-color: #fcfdfd;
        background-color: #fff;
        border-color: #dee5e7;
        border-bottom-color: #d8e1e3;
        -webkit-box-shadow: 0 1px 1px rgb(90 90 90 / 10%);
        box-shadow: 0 1px 1px rgb(90 90 90 / 10%);
    }

# 支持站点
1. 钉钉
2. QQ
3. 百度
4. gitee码云
5. github
6. 微博
7. 华为
8. Gitlab
9. 阿里云
10. 支付宝
11. 小米
12. 开源中国
13. 领英

会陆续新增其他站点的支持

# 版本
v2.1.2
1. 解决非伪静态下不能使用问题 
2. 调整图标问题
3. 修复已知道bug

v2.1.1
1. 增加领英登录 
2. 增加后台设置第三方登录单独控制开关
3. 紧急修复写入创建表bug

v2.0.3
1. 增加小米&开源中国登录 
2. 同步绑定设置typecho样式

v2.0.2
1. 增加支付宝 阿里云 账号登录
2. 修复已知bug

v2.0.1
1. 优化已知bug
2. 新增 gitlab 与 华为 第三方登录

v2.0 
1. 取消登录账号绑定
2. 新增加后台用户绑定功能开启插件即可看见
3. 优化已知bug
