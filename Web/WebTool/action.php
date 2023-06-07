<?php

/*
* 名称：R2登录账号接口
* 日期：2022-11-21
* 描述：通过此接口，实现用户的登录、注册、改密等等相关操作
* 警告：为了接口安全，请勿将此接口直接暴露在公网，否则可能会引起安全问题
*/

require_once dirname(__FILE__) . "/config/common.php";

// 请求形如：http://127.0.0.1/R2/action?type=login&username=123123&password=123123&ip=127.0.0.1}&mac=4E-A6-ED-B1&server=5

if(!isset($_GET['type'])) die_xml('FAIL', '缺少必要参数！');    // type参数是必须的

switch($_GET['type'])
{
    case 'login':
        // 登录账号
        // die_xml('FAIL', '登录已关闭！');   // 如果要禁用登录功能，请删除本行前面的“//”并保存即可。
        $username  = $_GET['username']; // 游戏账号
        $password  = $_GET['password']; // 登录密码
        $client_ip = $_GET['ip'];       // 客户端地址
        $machine   = $_GET['mac'];      // 机器码
        $server_id = (int)$_GET['server'];  // 选择的服务器
        $step = (int)$_GET['step']; // 登录的步骤，用于区分是哪里登录的
        if(!$username) die_xml('FAIL', '账号无效！');
        if(!$password) die_xml('FAIL', '密码无效！');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_xml('FAIL', '账号格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_xml('FAIL', '密码格式错误！仅限英文、数字和下划线组合。');
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        
        $sql = "SELECT * FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='" . $username . "' AND accountPassword='" . $password . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if($row['accountName'] != $username) die_xml('FAIL', '游戏账号或登录密码错误！');
        //$str = base64_encode(AES::encrypt(sha1($password), $username));
		$guid = strtoupper(md5(time() . rand(0,9) . rand(0,9) . rand(0,9)));
		$sql = "UPDATE GlobalDB_Create.dbo.LocalAccount SET AccountName='" . $guid . "' WHERE USN=" . $row['usn'];
		if(!$sqlsrv->run($sql)) die_xml('FAIL', '登陆服务器内部错误！');
        die_xml('SUCCESS', $guid);
    case 'register':
        // 注册账号
        // die_xml('FAIL', '注册功能已关闭！');   // 如果要禁用注册功能，请删除本行前面的“//”并保存即可。
        $username  = $_GET['username']; // 游戏账号
        $password  = $_GET['password']; // 登录密码
        $superpswd  = $_GET['superpswd']; // 登录密码
        $client_ip = $_GET['ip'];       // 客户端地址
        $machine   = $_GET['mac'];      // 机器码
        $server_id = (int)$_GET['server'];  // 选择的服务器
        $email = $_GET['email'];  // 邮件地址
        if(!$username) die_xml('FAIL', '账号无效！');
        if(!$password) die_xml('FAIL', '密码无效！');
        if(!$superpswd) die_xml('FAIL', '超级密码无效！');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_xml('FAIL', '账号格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_xml('FAIL', '密码格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $superpswd)) die_xml('FAIL', '超级密码格式错误！仅限英文、数字和下划线组合。');
        //if(!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email)) die_xml('FAIL', '电子邮件地址错误！');
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = "SELECT COUNT(*) AS cCount FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='" . $username . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if($row['cCount'] > 0) die_xml('FAIL', '此账号已存在！');
        //$sql = "SELECT COUNT(*) AS cCount FROM FNLAccount.dbo.Member WHERE email='" . $email . "';";
        //if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        //$row = $sqlsrv->next();
        //if($row['cCount'] > 0) die_xml('FAIL', '此邮件地址已存在！');
		$usn = time() . rand(0,9) . rand(0,9) . rand(0,9);
        $sql = "INSERT INTO GlobalDB_Create.dbo.Web_Account (usn, accountName, accountPassword) VALUES ({$usn}, '{$username}', '{$password}');";
		
		$sql .= "INSERT INTO GlobalDB_Create.dbo.LocalAccount (USN, AccountName, REGDATE) VALUES ({$usn}, '{$username}', GETDATE());";
		$sql .= "INSERT INTO GlobalDB_Create.dbo.Account (USN,GMLevel,REGDATE,lastAccessServerId) VALUES ({$usn},0,GETDATE(),101);";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());

        die_xml('SUCCESS', '恭喜您！账号注册成功！');

    case 'reset_superpswd':
        // 超密改密
        // die_xml('FAIL', '超级密码改密功能已关闭，请使用其他方式改密！');   // 如果要禁用超级密码方式改密，请删除本行前面的“//”并保存即可。
        // http://127.0.0.1/R2/action?type=reset_superpswd&username=123123&password=123123&superpswd=123123&ip=127.0.0.1&mac=4E-A6-ED-B1&server=1&email={email} 
        $username = $_GET['username'];
        $password = $_GET['password'];
        $superpswd=$_GET['superpswd'];
        $server_id =(int)$_GET['server'];
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_xml('FAIL', '账号格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_xml('FAIL', '密码格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $superpswd)) die_xml('FAIL', '超级密码格式错误！仅限英文、数字和下划线组合。');
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = "SELECT COUNT(*) AS cCount FROM FNLAccount.dbo.Member WHERE mUserId='" . $username . "' AND Superpwd='" . $superpswd . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if($row['cCount'] <= 0) die_xml('FAIL', '账号或超级密码错误！');
        $sql = "UPDATE FNLAccount.dbo.Member SET mUserPswd='" . $password . "' WHERE mUserId='" . $username . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        die_xml('SUCCESS', '您的登录密码已修改，下一次登录游戏时生效。');
    case 'reset_private_card':
        // 密保卡改密
        // die_xml('FAIL', '密保卡改密功能已关闭，请使用其他方式改密！');   // 如果要禁用密保卡方式改密，请删除本行前面的“//”并保存即可。
        // http://127.0.0.1/R2/action?type=reset_private_card&username=123123&password=123123&superpswd={superpswd}&ip=127.0.0.1&mac=4E-A6-ED-B1&server=1&email={email} 
        $username = $_GET['username'];
        $password = $_GET['password'];
        $server_id =(int)$_GET['server'];
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_xml('FAIL', '账号格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_xml('FAIL', '密码格式错误！仅限英文、数字和下划线组合。');
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = "SELECT COUNT(*) AS cCount FROM FNLAccount.dbo.Member WHERE mUserId='" . $username . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if($row['cCount'] <= 0) die_xml('FAIL', '账号服务器返回账号不存在！');
        $sql = "UPDATE FNLAccount.dbo.Member SET mUserPswd='" . $password . "' WHERE mUserId='" . $username . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        die_xml('SUCCESS', '您的登录密码已修改，下一次登录游戏时生效。');
    case 'reset_mail':
        // 邮件改密
        // die_xml('FAIL', '邮箱验证码改密功能已关闭，请使用其他方式改密！');   // 如果要禁用邮箱验证码方式改密，请删除本行前面的“//”并保存即可。
        // http://127.0.0.1/R2/action?type=reset_mail&username=123123&password=123123&superpswd={superpswd}&ip=127.0.0.1&mac=4E-A6-ED-B1&server=1&email=iqiye@qq.com 
        $username = $_GET['username'];
        $password = $_GET['password'];
        $email = $_GET['email'];
        $server_id =(int)$_GET['server'];
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $username)) die_xml('FAIL', '账号格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/^[A-Za-z0-9_]{1,30}$/", $password)) die_xml('FAIL', '密码格式错误！仅限英文、数字和下划线组合。');
        if(!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/", $email)) die_xml('FAIL', '电子邮件地址错误！');
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = "SELECT COUNT(*) AS cCount FROM FNLAccount.dbo.Member WHERE mUserId='" . $username . "' AND email='" . $email . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if($row['cCount'] <= 0) die_xml('FAIL', '账号或邮箱地址错误！');
        $sql = "UPDATE FNLAccount.dbo.Member SET mUserPswd='" . $password . "' WHERE mUserId='" . $username . "';";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        die_xml('SUCCESS', '您的登录密码已修改，下一次登录游戏时生效。');
    case 'get_ranking_data':
        // 取排行数据
        $server_id =(int)$_GET['server'];
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        if(!is_dir(dirname(__FILE__) . "/rank_logs/")) mkdir(dirname(__FILE__) . "/rank_logs/");
        $path = dirname(__FILE__) . "/rank_logs/ranking_server_" . $server_id . ".json";
        if(file_exists($path)){
            $rank_data = json_decode(file_get_contents($path), true);
            if(is_array($rank_data)){
                if(time() - $rank_data['update_date'] < 600){
                    // 有效期内，直接返回数据
                    die_xml('SUCCESS', 'ok!', ['data' => $rank_data['ranking_data']]);
                }
            }
        }
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = "SELECT TOP 20 t2.mNo, ISNULL(t2.mNm, N'') AS mNm, t1.mLevel, t1.mExp, t2.mClass, t2.mSex, ISNULL(t4.mGuildNm, N'-') AS mGuildNm FROM FNLGame2155.dbo.TblPcState AS t1 INNER JOIN FNLGame2155.dbo.TblPc AS t2 ON t2.mNo = t1.mNo FULL JOIN FNLGame2155.dbo.TblGuildMember AS t3 ON t3.mPcNo = t1.mNo FULL JOIN FNLGame2155.dbo.TblGuild AS t4 ON t4.mGuildNo = t3.mGuildNo WHERE t2.mDelDate IS NULL ORDER BY t1.mLevel DESC, t1.mExp DESC, t2.mRegDate ASC;";
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '排行榜服务器内部错误！' . $sqlsrv->error());
        $arr = [];
        $count = 0;
        while($row = $sqlsrv->next())
        {
            $count++;
            if(is_array($rank_data['ranking_data'])){
                $up = 0; // -1掉榜，0：持平，1:上榜
                foreach($rank_data['ranking_data'] as $tmp)
                {
                    if($tmp['CharNo'] == $row['mNo'])
                    {
                        if($count > $tmp['Ranking']){
                            $up = -1;
                        }if($count < $tmp['Ranking']){
                            $up = 1;
                        }else{
                            $up = 0;
                        }
                        break;
                    }
                }
            }else{
                $up = 1;
            }
            $arr[] = [
                'CharNo' => $row['mNo'],
                'Ranking'=> $count,
                'Change' => $up,
                'CharNm' => trim($row['mNm']),
                'Lev' => $row['mLevel'],
                'Exp' => $row['mExp'],
                'Sex' => ($row['mSex'] == 0 ? '男' : '女'),
                'Class' => R2GetClassNm((int)$row['mClass']),
                'GuildNm' => trim($row['mGuildNm']),
            ];
        }

        $data = [
            'update_date' => time(),
            'ranking_data' => $arr
        ];
        
        file_put_contents($path, json_encode($data));
        die_xml('SUCCESS', 'ok!', ['data' => $arr]);
    
    case 'add_mac_lock_verify':
        // 增加设备锁的验证
        // http://139.155.77.238:8050/R2/action?type=add_mac_lock_verify&username=123123&password={password}&superpswd=123123&ip=127.0.0.1&mac=4E-A6-ED-B1&server=2&email={email}
        $username = $_GET['username'];
        $superpswd = $_GET['superpswd'];
        $server_id =(int)$_GET['server'];
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = sprintf("SELECT * FROM FNLAccount.dbo.Member WHERE mUserId='%s' AND Superpwd='%s';", $username, $superpswd);
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if(trim($row['mUserId']) != $username) die_xml('FAIL', '账号或超级密码错误！');
        die_xml('SUCCESS', '验证通过！');
    case 'delete_all_mac_lock_verify':
        // 删除设备锁的验证
        // http://139.155.77.238:8050/R2/action?type=delete_all_mac_lock_verify&username=123123&password={password}&superpswd=123123&ip=127.0.0.1&mac=4E-A6-ED-B1&server=2&email={email} 
        $username = $_GET['username'];
        $superpswd = $_GET['superpswd'];
        $server_id =(int)$_GET['server'];
        $json = json_decode(file_get_contents(dirname(__FILE__) . "/config/ServerInfo.json"), true);
        if(!is_array($json)) die_xml('FAIL', '服务器配置文件错误！');
        $exists = null;
        foreach($json as $data)
        {
            if($data['id'] == $server_id)
            {
                $exists = true;
                break;
            }
        }
        if(!$exists) die_xml('FAIL', '万分抱歉！没有找到所选择服务器对应账号服务器的信息，请选择其他服务器再次尝试！');
        $sqlsrv = new SqlServer(
            $data['sqlserver']['host'],
            $data['sqlserver']['user'],
            $data['sqlserver']['pswd'],
            $data['sqlserver']['db_acc']
        );
        if(!$sqlsrv) die_xml('FAIL', '连接账号服务器失败！');
        $sql = sprintf("SELECT * FROM FNLAccount.dbo.Member WHERE mUserId='%s' AND Superpwd='%s';", $username, $superpswd);
        if(!$sqlsrv->run($sql)) die_xml('FAIL', '账号服务器内部错误！' . $sqlsrv->error());
        $row = $sqlsrv->next();
        if(trim($row['mUserId']) != $username) die_xml('FAIL', '账号或超级密码错误！');
        die_xml('SUCCESS', '验证通过！');
    default:
        die_xml('FAIL', '不受支持的请求类型！');
}

function R2GetClassNm($class_id)
{
    $arr = ['骑士', '游侠', '精灵', '刺客', '召唤'];
    return ($class_id >= count($arr) ? '未知' : $arr[$class_id]);
}