<?php

/**
 * 引入所需要的文件
 */
require_once dirname(__FILE__) . '/inc/config.php'; // 配置文件
require_once dirname(__FILE__) . '/inc/function.php';   // 通用功能文件
require_once dirname(__FILE__) . '/inc/discuz.authcode.php';    // 数组加密文件
require_once dirname(__FILE__) . '/inc/sqlserver.class.php';    // sqlserver类
require_once dirname(__FILE__) . '/inc/game.func.php';   // 游戏功能文件

$sqlserver = new SqlServer(SQLSERVER_CONFIG);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BLESS Mall</title>
    <link rel="stylesheet" href="css/mall.css">
    <link rel="stylesheet" href="css/all.min.css">
    <script src="js/jquery-3.5.1.min.js"></script>
    <script src="js/mall.min.js"></script>
    <script src="js/base64.min.js"></script>
</head>

<body class="mall-body">
    <div class="mall-top-bar">
        <div>
            <div class="mall-srcoll-info-box">
                <div>欢迎来到神佑释放（BLESS） WEB商城，丰富的商品和超值礼包等你开启！</div>
            </div>
        </div>
        <div class="user-box">
            <?php
            if(UserIsLogin())
            {
                echo '您好！当前登录：' . GetLoginUserName() . '（角色：<span style="color:red;">' . GetLoginCharName() . '</span> 点券：<span style="color:yellow;" id="user-cash">' . GetUserMoney($sqlserver, GetLoginUserName()) . '</span>） | <a href="javascript:;" onclick="UserLogout.Logout(\'' . encry(['TYPE' => 'LOGOUT', 'USERNAME' => GetLoginUserName()]) . '\')">退出登录</a>';
            }else{
                echo '您好！请登录！';
            }
            ?>
        </div>
    </div>
    <div class="mall mall-clip">
        <div class="mall-menu">
            <ul>
                <li jump-action="<?php echo encry(['TYPE' => 'GET_MALL', 'MALL_TYPE' => 1, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-shopping-bag"></i> 特惠</li>
                <li jump-action="<?php echo encry(['TYPE' => 'GET_MALL', 'MALL_TYPE' => 0, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-shopping-cart"></i> 商城</li>
                <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 3, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-calendar-day"></i> 日充</li>
<!--                <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 4, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-calendar-week"></i> 周充</li>-->
                <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 5, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-gem"></i> 累充</li>
                <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 0, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-gift"></i> 日礼</li>
                
<!--                <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 1, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-gift-card"></i> 周礼</li>-->
               <li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 2, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-gifts"></i> 免费</li>
                <!--<li jump-action="<?php echo encry(['TYPE' => 'GET_GAME_PACKET', 'MALL_TYPE' => 6, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-fire"></i> 直购</li>-->
                
 <!--               <li jump-action="<?php echo encry(['TYPE' => 'GET_PAY', 'MALL_TYPE' => 0, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-piggy-bank"></i>购卡</li>-->
                <li jump-action="<?php echo encry(['TYPE' => 'GET_ACTIVE_CODE', 'MALL_TYPE' => 0, 'TIMES' => time()]) ?>"><i class="far fa-fw fa-credit-card-front"></i> 激活码</li>
            </ul>
        </div>
        <div class="mall-goods">
            
        </div>
    </div>
    <script>
        $(document).ready(function() {
            <?php
            if (!UserIsLogin()) {
                echo 'showLoginBox("' . encry(['TYPE' => 'LOGIN']) . '");'; // 输出登录框
            } else {
                echo "$('.mall-menu li').eq(0).click();";
            }
            ?>
        });
    </script>
</body>

</html>