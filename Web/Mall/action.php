<?php
/**
 * 引入所需要的文件
 */
require_once dirname(__FILE__) . '/inc/config.php'; // 配置文件
require_once dirname(__FILE__) . '/inc/function.php';   // 通用功能文件
require_once dirname(__FILE__) . '/inc/discuz.authcode.php';    // 数组加密文件
require_once dirname(__FILE__) . '/inc/sqlserver.class.php';    // sqlserver类
require_once dirname(__FILE__) . '/inc/game.func.php';   // 游戏功能文件

// 开始处理逻辑事务
$arr = $_POST;
if(!isset($arr['action'])) die_json('fail', '缺少action参数！');
$action_arr = decry($arr['action']);
if(!is_array($action_arr)) die_json('fail', 'action参数错误！');
switch ($action_arr['TYPE'])
{
    case 'LOGOUT':  // 登出账号
        if($action_arr['USERNAME'] != GetLoginUserName()) die_json('fail', '登出失败！<br>可能是登录状态已发生变化，请刷新页面后再试！');
        UserLogout();
        die_json('success', '退出登录成功，页面即将刷新！');
    case 'LOGIN':   // 登录账号
        $username = $arr['username'];   // 需要从post获取
        $password = $arr['password'];   // 需要从post获取
        // 校验账号密码长度，避免正则的漏洞
        if(strlen($username) < 1 || strlen($username) > 30) die_json('fail', '用户名长度错误！');
        if(strlen($password) < 1 || strlen($password) > 30) die_json('fail', '密码长度错误！');
        // 正则匹配账号密码
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_json('fail', '账号仅限英文字母、数字和下划线！');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_json('fail', '密码仅限英文字母、数字和下划线！');
        // 账号密码校验通过，建立与数据库的连接
        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！' . $sqlserver->error());
        // 连接数据库成功，开始查询账号
        $sql = sprintf(
            "SELECT * FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='%s' AND accountPassword='%s';",
            $username,
            $password
        );
        if(!$sqlserver->run($sql)) die_json('fail', '查询数据库失败！' . $sqlserver->error());
        $row = $sqlserver->next();
        if($row['accountName'] != $username) die_json('fail', '账号或密码错误！');
        SetUserLogin($row['accountName'], $row['usn'], 1);
        //if(!UserIsLogin()) die_json('fail', '登录失败！');
        // 发送账号下得到角色给用户选择
        $chars_arr = GetUserChars($sqlserver, $username);
        if(count($chars_arr) == 0) die_json('fail', '此账号下无角色，请先在游戏中创建角色再登录！');
        if(isset($arr['char']) && $arr['char'] != ''){
            foreach($chars_arr as $char)
            {
                if($arr['char'] == $char['usn'])
                {
                    SetUserLogin($row['accountName'], $row['usn'], 2, $char['usn'], $char['name']);
                    die_json('success', '登录成功！');
                }
            }
            die_json('fail', '所选择的角色与当前账号无任何关系，请重新登录并选择角色再登录！');
        }
        die_json('select', '请选择角色！', $chars_arr);
        
    case 'GET_MALL':    // 获取商城的HTML
        if(!UserIsLogin()) die_json('fail', '未登录，请先登录！');
        if(!isset($action_arr['MALL_TYPE'])) die_json('fail', 'action错误！');
        
        $page = (int)$arr['page'];
        $json_arr = json_decode(file_get_contents(dirname(__FILE__) . '/json/mall.json'), true);
        if(!is_array($json_arr)) die_json('fail', 'mall.json文件不存在或不是标准的json格式文件或编码（需为UTF8编码，无BOM）错误！');
        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');
        $page = ($page < 1 ? 1 : $page);
        $ret_arr = [];
        $count = 0;
        $index = 0;
        foreach($json_arr as $tmp)
        {
            if($tmp['mall_type'] == $action_arr['MALL_TYPE'])
            {
                $count ++;
                if(ceil($count / 8) == $page)
                {
                    $ret_arr[] = [
                        // 展示用的base64
                        "item" => base64_encode(json_encode([
                            "token" => encry(['TYPE' => 'MALL_BUY', 'INDEX' => (int)$tmp['serial']]),
                            "name" => $tmp['item']['name'],
                            "old_price" => $tmp['item']['old_price'],
                            "new_price" => $tmp['item']['new_price'],
                            "unit" => $tmp['item']['unit'],
                            "color" => (int)$tmp['item']['color'],
                            "icon_url" => $tmp['item']['icon_url'],
                            "attach_name" => $tmp['item']['attach_name'],
                            "content" => $tmp['item']['content']
                        ]))
                    ];
                }
                
            }
            $index ++;
        }
        // 随机取2个热销的
        $hot_arr = [];
        foreach($json_arr as $tmp)
        {
            if($tmp['hot'])
            {
                $hot_arr[] = $tmp;
            }
        }
        $hot_goods_arr = [];
        if(count($hot_arr) > 0)
        {
            $idx_arr = array_rand($hot_arr, 2);
            foreach($idx_arr as $idx)
            {
                $hot_goods_arr[] = [
                    // 展示用的base64
                    "item" => base64_encode(json_encode([
                        "token" => encry(['TYPE' => 'MALL_BUY', 'INDEX' => (int)$json_arr[$idx]['serial']]),
                        "name" => $json_arr[$idx]['item']['name'],
                        "old_price" => $json_arr[$idx]['item']['old_price'],
                        "new_price" => $json_arr[$idx]['item']['new_price'],
                        "unit" => $json_arr[$idx]['item']['unit'],
                        "color" => (int)$json_arr[$idx]['item']['color'],
                        "icon_url" => $json_arr[$idx]['item']['icon_url'],
                        "attach_name" => $json_arr[$idx]['item']['attach_name'],
                        "content" => $json_arr[$idx]['item']['content']
                    ]))
                ];
            }
        }
        if(count($ret_arr) == 0) die_json('fail', '已无更多商品！');
        $all_page = ceil($count / 8);
        die_json('success', 'ok', [
            "action" => "mall",
            "token" => encry(['TYPE' => 'GET_MALL', 'MALL_TYPE' => $action_arr['MALL_TYPE']]),
            "mall_type" => $action_arr['MALL_TYPE'],
            "page" => $page,
            "all_page" => $all_page,
            "goods" => $ret_arr,
            "hot" => $hot_goods_arr
        ]);

    case 'MALL_BUY':    // 购买商品
        if(!UserIsLogin()) die_json('fail', '未登录！请先登录！');
        if(!isset($action_arr['INDEX']) || (int)$action_arr['INDEX'] == 0) die_json('fail', '购买失败！<br>此商品缺少serial属性！');
        $json_arr = json_decode(file_get_contents(dirname(__FILE__) . '/json/mall.json'), true);
        if(!is_array($json_arr)) die_json('fail', 'mall.json文件不存在或不是标准的json格式文件或编码（需为UTF8编码，无BOM）错误！');
        $exists = false;
        foreach($json_arr as $goods)
        {
            if($goods['serial'] == $action_arr['INDEX'])
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_json('fail', '未找到此商品！');
        // 连接数据库
        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');
        $user_cash = GetUserMoney($sqlserver, GetLoginUserName());
        if($user_cash < $goods['item']['new_price']) die_json('fail', '<span style="color:red;">' . $goods['item']['unit'] . '</span>不足！');
        // 扣减点数
        if($goods['item']['new_price'] > 0){
            if(!MinusUserCash($sqlserver, GetLoginUserName(), $goods['item']['new_price'])) die_json('fail', '扣除<span style="color:red;">' . $goods['item']['unit'] . '</span>失败！');
        }
        
        // 拼装，看下有没有货币
        if(isset($goods['item']['currency']))
        {
            $sql = "EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail 
            " . GetLoginCharId() . "
            ,0
            ,0
            ,N'系统'
            ,253
            ,1
            ,N'WEB商城'
            ,N'[]'
            ," . (int)$goods['item']['currency']['Gold'] . "
            ," . (int)$goods['item']['currency']['Starseed'] . "
            ," . (int)$goods['item']['currency']['Relic_Fragments'] . "
            ," . (int)$goods['item']['currency']['Relic_Core'] . "
            ," . (int)$goods['item']['currency']['Crystal'] . "
            ," . (int)$goods['item']['currency']['Seal'] . "
            ," . (int)$goods['item']['currency']['Mint'] . "
            ," . (int)$goods['item']['currency']['Brand'] . "
            ," . (int)$goods['item']['currency']['BattleCoin'] . "
            ," . (int)$goods['item']['currency']['RevivalCoin'] . "
            ," . (int)$goods['item']['currency']['NormalKey'] . "
            ," . (int)$goods['item']['currency']['EliteKey'] . "
            ," . (int)$goods['item']['currency']['ValorsTalent'] . "
            ," . (int)$goods['item']['currency']['Exp'] . "
            ," . (int)$goods['item']['currency']['ReputationCid'] . "
            ," . (int)$goods['item']['currency']['ReputationValue'] . "
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,0
            ,N'" . date('Y-m-d H:i:s') . "'
            ,N'2079-01-01 00:00:00'
            ,NULL";
            $sqlserver->run($sql);
        }
        $send_arr = [];
        $tmp_arr = [];
        for($i = 0; $i < count($goods['item']['info']); $i++)
        {
            if($i != 0 && $i % 4 == 0)
            {
                $send_arr[] = $tmp_arr;
                $tmp_arr = [];
            }
            $tmp_arr[] = $goods['item']['info'][$i];
            
        }
        if(count($tmp_arr) > 0) $send_arr[] = $tmp_arr;
        foreach($send_arr as $send)
        {
            $str = '';
            $count = 0;
            foreach($send as $item)
            {
                $count ++;
                $str .= sprintf(
                    ',0,%s,%d,%d,%d',
                    $item['item_id'],
                    $item['num'],
                    $item['enchant'],
                    $item['grade']
                );
            }
            for($i = 0; $i < (4 - $count); $i++)
            {
                $str .= ',0,0,0,0,0';
            }
            // 拼装SQL
            $sql = "EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail 
                " . GetLoginCharId() . "
                ,0
                ,0
                ,N'系统'
                ,253
                ,1
                ,N'WEB商城'
                ,N'[]'
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                ,0
                " . $str . "
                ,N'" . date('Y-m-d H:i:s') . "'
                ,N'2079-01-01 00:00:00'
                ,NULL";
            if(!$sqlserver->run($sql)) die_json('fail', '购买失败！' . $sqlserver->error());
        }
        $sql = "INSERT INTO create_gamedb_pc.WebMall.TBL_MallBuyHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_INDEX, FLD_IP, FLD_PRICE, FLD_BALANCE) VALUES ('" . GetLoginUserName() ."', " . GetLoginCharId() . ", " . $goods['serial'] . ", '" . get_real_ip() . "', " . $goods['item']['new_price'] . ", " . ($user_cash - $goods['item']['new_price']) .");";
        $sqlserver->run($sql); // 添加记录
        die_json('success', '购买成功！<br>请在游戏内的邮箱中领取！', ['user_cash' => GetUserMoney($sqlserver, GetLoginUserName())]);

    case 'GET_GAME_PACKET' :    // 获取礼包信息
        if(!UserIsLogin()) die_json('fail', '未登录！请先登录！');
        $json_arr = json_decode(file_get_contents(dirname(__FILE__) . '/json/game_packet.json'), true);
        if(!is_array($json_arr)) die_json('fail', 'game_packet.json文件不存在或不是标准的json格式文件或编码（需为UTF8编码，无BOM）错误！');
        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');

        $ret_arr = [];  // 声明空数组
        foreach($json_arr as $item)
        {
            if((int)$item['type'] == (int)$action_arr['MALL_TYPE'] && $item['use'])
            {
                $tmp_arr = [];
                foreach($item['item']['info'] as $tmp)
                {
                    $tmp_arr[] = [
                        'item' => base64_encode(json_encode([
                            "name" => $tmp['name'],
                            "num" => $tmp['num'],
                            "color" => (int)$tmp['color'],
                            "icon_url" => $tmp['icon_url'],
                            "attach_name" => $tmp['attach_name'],
                            "content" => $tmp['content']
                        ]))
                    ];
                }
                if(count($tmp_arr) > 0)
                {
                    if(($item['date_start'] != 0 && time() > $item['date_start']) || ($item['date_end'] != 0 && time() < $item['date_end']))
                    {
                        $ret_arr[] = [
                            // 展示用的base64
                            "item" => base64_encode(json_encode([
                                "token" => encry(['TYPE' => 'SET_GAME_PACKET', 'INDEX' => (int)$item['serial']]),
                                "name" => $item['name'],
                                "packet" => $tmp_arr,
                                "old_price" => $item['old_price'],
                                "new_price" => $item['new_price'],
                                "limit_type" => $item['limit_type'],
                                "limit_num" => $item['limit_max'],
                                "current_cash" => (int)GetUserRechargeCash(
                                    $sqlserver,
                                    (int)$item['limit_type'],
                                    GetLoginUserName(),
                                    ($item['date_start'] > 0 ? $item['date_start'] : null),
                                    ($item['date_end'] > 0 ? $item['date_end'] : null)
                                ),
                                "surplus_num" => (int)GetMallPacketSellCount(
                                    $sqlserver, 
                                    (int)$item['serial'], 
                                    (int)$item['limit_type'], 
                                    (int)$item['limit_class'],
                                    GetLoginUserName(),
                                    GetLoginCharId(),
                                    ($item['limit_max'] > 0 ? $item['limit_max'] : null)
                                ),
                                "unit" => $item['unit'],
                                "end_times" => ($item['date_end'] != 0 ? $item['date_end'] - time() : 0)
                            ]))
                        ];
                    }
                    
                }
                
            }
        }
        if(count($ret_arr) == 0) die_json('fail', '无更多礼包信息！');
        die_json('success', 'ok', [
            "action" => "packet",
            "type" => $action_arr['MALL_TYPE'],
            "list" => $ret_arr
        ]);

    case 'SET_GAME_PACKET': // 礼包购买或领取
        if(!UserIsLogin()) die_json('fail', '未登录！请先登录！');
        $json_arr = json_decode(file_get_contents(dirname(__FILE__) . '/json/game_packet.json'), true);
        if(!is_array($json_arr)) die_json('fail', 'game_packet.json文件不存在或不是标准的json格式文件或编码（需为UTF8编码，无BOM）错误！');
        $exists = false;
        foreach($json_arr as $item)
        {
            if($item['serial'] == $action_arr['INDEX'])
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_json('fail', '找不到对应礼包！');
        if(!$item['use']) die_json('fail', '该礼包已下线！');
        if($item['date_start'] != 0 && time() < $item['date_start']) die_json('fail', '该礼包还未开始发行，该礼包将在<span style="color:#0f0;">[' . date('Y-m-d H:i:s', $item['date_start']) . ']</span>发行！');
        if($item['date_end'] != 0 && time() > $item['date_end']) die_json('fail', '该礼包已过期，该礼包已在<span style="color:#0f0;">[' . date('Y-m-d H:i:s', $item['date_end']) . ']</span>发行！');

        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');

        if($item['limit_max'] > 0)
        {
            $surplus_num = (int)GetMallPacketSellCount(
                $sqlserver, 
                (int)$item['serial'], 
                (int)$item['limit_type'], 
                (int)$item['limit_class'],
                GetLoginUserName(),
                GetLoginCharId(),
                ($item['limit_max'] > 0 ? $item['limit_max'] : null)
            );
            if($surplus_num <= 0) die_json('fail', '礼包已被领完了！');
        }
        if($item['type'] >= 3 && $item['type'] <= 5)
        {
            // 充值礼包，判断充值金额是否符合领取条件
            $cash = (int)GetUserRechargeCash(
                $sqlserver,
                (int)$item['limit_type'],
                GetLoginUserName(),
                ($item['date_start'] > 0 ? $item['date_start'] : null),
                ($item['date_end'] > 0 ? $item['date_end'] : null)
            );
            if($cash < (int)$item['new_price']) die_json('fail', '充值<span style="color:red;">' . $item['unit'] . '</span>不足！');
        }elseif($item['type'] >= 0 && $item['type'] <= 2)
        {
            // 购买类礼包，只有购买金额大于0的才比较
            if($item['new_price'] > 0)
            {
                if(GetUserMoney($sqlserver, GetLoginUserName()) < $item['new_price']) die_json('fail', '<span style="color:red;">' . $item['unit'] . '</span>不足！无法购买！');
                if(!MinusUserCash($sqlserver, GetLoginUserName(), $item['new_price'])) die_json('fail', '购买失败！');  //
            }
        }elseif($item['type'] == 6){
            // 直购类礼包，这里应该要返回直购的链接，但是SF就是麻烦，所以这里就不做处理了
            die_json('fail', '直购未开启！');
        }else{
            die_json('fail', '不支持的type！(' . $item['type'] . ')');
        }
        // 到这里可以发物品了
        
        if($item['item']['currency'])
        {
            SendGameCurrency($sqlserver, GetLoginCharId(), $item['item']['currency']);
        }
        if(count($item['item']['info']) > 0)
        {
            SendGameItem($sqlserver, GetLoginCharId(), $item['item']['info']);
        }
        // 记录SQL
        AddPacketHistory($sqlserver, GetLoginUserName(), GetLoginCharId(), $item);

        die_json('success', '领取成功！<br>请在游戏内的邮箱中领取！', ['user_cash' => GetUserMoney($sqlserver, GetLoginUserName())]);

    case 'GET_ACTIVE_CODE': // 激活码，因为前端展示页面，所以这里直接返回
        if(!UserIsLogin()) die_json('fail', '未登录！请先登录！');

        die_json('success','ok',[
            "action" => "active_code",
            "token" => encry(["TYPE" => 'SET_ACTIVE_CODE']),
        ]);
    case 'SET_ACTIVE_CODE': // 使用激活码
        if(!UserIsLogin()) die_json('fail', '未登录！请先登录！');
        if(!isset($arr['active_code'])) die_json('fail', '缺少active_code字段！');
        if(strlen($arr['active_code']) <= 0 || strlen($arr['active_code']) > 30) die_json('fail', '激活码长度错误！');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $arr['active_code'])) die_json('fail', '激活码格式错误，激活码仅限英文字符、数字和下划线！');
        // 激活码在数据库查询
        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');
        $sql = "SELECT * FROM create_gamedb_pc.WebMall.TBL_MallActiveCodeBase WHERE FLD_CODE='" . $arr['active_code'] . "';";
        if(!$sqlserver->run($sql)) die_json('fail', '服务器错误！');
        $row = $sqlserver->next();
        if($row['FLD_CODE'] != $arr['active_code']) die_json('fail', '激活码错误！');
        if($row['FLD_USE'] != 1) die_json('fail', '激活码已停用！');
        if($row['FLD_DATE_START'] != 0)
        {
            if(time() < $row['FLD_DATE_START']) die_json('fail', '激活码未到使用时间，请在<span style="color:red;">' . date('Y-m-d H:i:s', $row['FLD_DATE_START']) . '</span>后再使用！');
        }
        if($row['FLD_DATE_END'] != 0)
        {
            if(time() > $row['FLD_DATE_END']) die_json('fail', '激活码已过期！');
        }
        $use_count = GetMallActiveCodeUseCount($sqlserver, (int)$row['FLD_TYPE'], $row['FLD_CODE'], GetLoginUserName(), GetLoginCharId());
        if($use_count === null) die_json('fail', '激活码类型不被支持！');
        if($row['FLD_TYPE'] == 0 && $use_count > 0) die_json('fail', '激活码已被使用！');
        elseif($row['FLD_TYPE'] == 1 && $use_count > 0) die_json('fail', '激活码已被使用，此角色已无法继续使用！');
        elseif(($row['FLD_TYPE'] == 2 || $row['FLD_TYPE'] == 4) && $use_count > 0) die_json('fail', '激活码已被使用，此账号已无法继续使用！');
        
        // 检查冷却时间
        if($row['FLD_COOLING_TIMES'] > 0)
        {
            $seconds = GetMallActiveCodeCollingTime($sqlserver, $row['FLD_CODE'], $row['FLD_COOLING_TIMES'], $row['type'], GetLoginUserName(), GetLoginCharId());
            if($seconds > 0 && $seconds < $row['FLD_COOLING_TIMES']) die_json('fail', '此激活码还在冷却期间，请等待<span style="color:red;">' . Sec2Time($row['FLD_COOLING_TIMES'] - $seconds) . '</span>后再使用！');
        }
        $msg_str = '';
        // 检查通过，发送物品
        if($row['FLD_CURRENCY_JSON'])
        {
            SendGameCurrency($sqlserver, GetLoginCharId(), json_decode($row['FLD_CURRENCY_JSON'], true));
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得一些货币';
        }
        if($row['FLD_ITEM_JSON'])
        {
            SendGameItem($sqlserver, GetLoginCharId(), json_decode($row['FLD_ITEM_JSON'], true));
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得一些物品';
        }
        if($row['FLD_CASH'] > 0)
        {
            AddUserMoney($sqlserver, GetLoginUserName(), $row['FLD_CASH']);
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得了' . $row['FLD_CASH'] . "点券（已入账）";
            // 使用CDK加入充值记录
            $sql = "INSERT INTO create_gamedb_pc.WebMall.TBL_MallRechargeHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_CASH, FLD_DATE_REG, FLD_TRADE_ID, FLD_PAYMENT, FLD_STATE) VALUES ('" . GetLoginUserName() . "', " . GetLoginCharId() . ", " . (int)$row['FLD_CASH'] . ", GETDATE(), '" . $row['FLD_CODE'] . "','激活码方式', 'SUCCESS');";
            $sqlserver->run($sql);
        }
        // 添加记录
        AddMallActiveCodeHistory($sqlserver, $row['FLD_CODE'], GetLoginUserName(), GetLoginCharId());
        die_json('success', '使用成功！<br>' . $msg_str . '！', ['user_cash' => GetUserMoney($sqlserver, GetLoginUserName())]);
        // 以下代码是在json的
        /* 
        $json_arr = json_decode(file_get_contents(dirname(__FILE__) . '/json/active_code.json'), true);
        if(!is_array($json_arr)) die_json('fail', 'active_code.json文件不存在或不是标准的json格式文件或编码（需为UTF8编码，无BOM）错误！');
        $exists = false;
        foreach($json_arr as $active_code)
        {
            if($active_code['code'] == $arr['active_code'])
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_json('fail', '激活码不存在！');
        if(!$active_code['use']) die_json('fail', '激活码已被停用！');
        if($active_code['date_start'] != 0)
        {
            if(time() < $active_code['date_start']) die_json('fail', '激活码未到使用时间，请在<span style="color:red;">' . date('Y-m-d H:i:s', $active_code['date_start']) . '</span>后再使用！');
        }
        if($active_code['date_end'] != 0)
        {
            if(time() > $active_code['date_end']) die_json('fail', '激活码已过期！');
        }

        $sqlserver = new SqlServer(SQLSERVER_CONFIG);
        if(!$sqlserver) die_json('fail', '连接数据库失败！');

        $use_count = GetMallActiveCodeUseCount($sqlserver, $active_code['type'], $active_code['code'], GetLoginUserName(), GetLoginCharId());
        if($use_count === null) die_json('fail', '激活码类型不被支持！');

         // 0表示激活码是一次性的（全服只能用一次）；
         // 1表示一个角色只能用一次（同账号下的其他角色仍然可以继续用，但不同的账号仍然可以继续使用）；
         // 2表示一个账号只能用一次（同账号下一个角色用了其他角色不能继续用，但不同的账号仍然可以继续使用）；
         // 3表示无限次使用（一个角色可以无限次的使用此激活码，同账号下可以不限制角色）；
         // 4表示无限次使用（同账号下只有一个角色能使用）
        if($active_code['type'] == 0 && $use_count > 0) die_json('fail', '激活码已被使用！');
        elseif($active_code['type'] == 1 && $use_count > 0) die_json('fail', '激活码已被使用，此角色已无法继续使用！');
        elseif(($active_code['type'] == 2 || $active_code['type'] == 4) && $use_count > 0) die_json('fail', '激活码已被使用，此账号已无法继续使用！');
        
        // 检查冷却时间
        if($active_code['colling_times'] > 0)
        {
            $seconds = GetMallActiveCodeCollingTime($sqlserver, $active_code['code'], $active_code['colling_times'], $active_code['type'], GetLoginUserName(), GetLoginCharId());
            if($seconds > 0 && $seconds < $active_code['colling_times']) die_json('fail', '此激活码还在冷却期间，请等待<span style="color:red;">' . Sec2Time($active_code['colling_times'] - $seconds) . '</span>后再使用！');
        }
        $msg_str = '';
        // 检查通过，发送物品
        if($active_code['currency'])
        {
            SendGameCurrency($sqlserver, GetLoginCharId(), $active_code['currency']);
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得一些货币';
        }
        if(count($active_code['item']) > 0)
        {
            SendGameItem($sqlserver, GetLoginCharId(), $active_code['item']);
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得一些物品';
        }
        if($active_code['cash'] > 0)
        {
            AddUserMoney($sqlserver, GetLoginUserName(), $active_code['cash']);
            $msg_str .= ($msg_str != '' ? '，' : '') . '获得了' . $active_code['cash'] . "点券（已入账）";
        }
        // 添加记录
        AddMallActiveCodeHistory($sqlserver, $active_code['code'], GetLoginUserName(), GetLoginCharId());
        die_json('success', '使用成功！<br>' . $msg_str . '！', ['user_cash' => GetUserMoney($sqlserver, GetLoginUserName())]);
        */
    default:    // 默认的处理，不过这里作用不大，因为在switch后面没有其他的处理代码了
        die_json('fail', '不受支持的TYPE类型[' . $action_arr['TYPE'] . ']！');
}
