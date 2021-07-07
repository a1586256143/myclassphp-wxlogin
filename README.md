# 微信登录小组件
#### 根据code获取用户信息 
```php
require_once 'vendor/autoload.php';
$appid = '';
$appSecret = '';
$app = new system\WxLogin($appid , $appSecret);
$user = $app->getUserInfo($_GET['code']);
var_dump($user);
```
#### 发起授权并拿到授权信息 
```php
require_once 'vendor/autoload.php';
$appid = '';
$appSecret = '';
$app = new system\WxLogin($appid , $appSecret);
if (isset($_GET['code']) && $_GET['state']){
    $user = $app->getUserInfo($_GET['code']);
    if (!$user) exit($app->getError());
    var_dump($user);
    exit();
}
echo $app->auth();
```
