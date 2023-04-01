# bluedot/composer

## 目录结构

遵循于 Laravel 最佳实践

```
├── config
├── database
├── resources
└── src
    ├── Application
    │   ├── Analysis
    │   ├── Auth
    │   ├── Push
    │   ├── System
    │   ├── Tag
    │   ├── User
    │   └── WeChat
    ├── Composer.php
    ├── ComposerServiceProvider.php
    ├── Console
    ├── Exceptions
    ├── Http # HTTP 核心库
    │   ├── BaseController.php # 基类公共控制器
    │   ├── Controller.php # 公共控制器
    │   ├── Kernel.php
    │   ├── Middleware
    │   │   ├── AuthOperationLog.php
    │   │   ├── Authenticate.php
    │   │   ├── AuthenticateAdmin.php
    │   │   └── RequestLog.php
    │   └── Traits
    │       ├── PlatformBindUser.php
    │       ├── Select.php
    │       └── Validate.php
    ├── RouteRegistrar.php # 注入一些公共路由
    ├── Support # 第三方包
    │   ├── Aip
    │   ├── Aliyun
    │   ├── Auth
    │   ├── Captcha
    │   ├── Crypt
    │   ├── Database
    │   ├── Excel
    │   ├── Lookstar
    │   ├── Mail
    │   ├── Redis
    │   └── Vhall.php
    └── helpers.php
```

## Package Support

- **[Laravel](https://learnku.com/docs/laravel/9.x)**
- **[Passport OAuth 认证](https://learnku.com/docs/laravel/9.x/passport/12270)**
- **[jwt-auth](https://jwt-auth.readthedocs.io/en/develop/)**
- **[laravel-query-builder HTTP 查询](https://spatie.be/docs/laravel-query-builder/v5/introduction)**
- **[laravel-permission 多权限](https://spatie.be/docs/laravel-permission/v5/introduction)**
- **[EasyWeChat 微信 SDK](https://easywechat.com/6.x/)**
