<?php
/**
 * 游戏功能模块
 */
function GetUserMoney($sqlserver,$username)
{
    $cash = 0;
    $sql = "SELECT uCash FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='" . $username . "';";
    if($sqlserver->run($sql))
    {
        $row = $sqlserver->next();
        $cash = (int)$row['uCash'];
    }
    return $cash;
}
function AddUserMoney($sqlserver, $username, $cash = 0)
{
    $sql = "UPDATE GlobalDB_Create.dbo.Web_Account SET uCash=uCash+" . (int)$cash . " WHERE accountName='" . $username . "';";
    $sqlserver->run($sql);
}
function GetUserChars($sqlserver, $username)
{
    $arr = [];
    $sql = "SELECT DB_ID,Player_Name FROM create_gamedb_pc.dbo.DBPlayer WHERE USN = (SELECT usn FROM GlobalDB_Create.dbo.Web_Account WHERE accountName='" . $username . "') AND Unreg_Flag=0;";
    if($sqlserver->run($sql))
    {
        while($row = $sqlserver->next())
        {
            $arr[] = [
                'usn' => $row['DB_ID'],
                'name' => $row['Player_Name']
            ];
        }

    }
    return $arr;
}
function MinusUserCash($sqlserver, $username, $cash)
{
    $sql = "UPDATE GlobalDB_Create.dbo.Web_Account SET uCash=uCash-" . (int)$cash . " WHERE accountName='" . $username . "';";
    return $sqlserver->run($sql);
}
/**
 * 取商城礼包的卖出数量
 *
 * @param [type] $sqlserver
 * @param integer $packet_serial    礼包索引
 * @param [type] $limit_type    礼包类型
 * @param [type] $limit_class   礼包分类，等于0时必须提供角色ID，等于1时必须提供账号
 * @param [type] $username  账号
 * @param [type] $char_id   角色ID
 * @param [type] $total_num   总数量，如果提供此参数，则用总数量减去结果数量返回剩余数量，否则返回结果数量
 * @return void
 */
function GetMallPacketSellCount($sqlserver, $packet_serial = 0, $limit_type = null, $limit_class = null, $username = null, $char_id = null, $total_num = null)
{
    /**
     * limit_type      ：限制类型，0=每日限制，1=每周限制，2=永久限制（永久时，date_start和date_end有效，均为时间戳）
     * limit_class     ：限制分类，0=单一角色，1=单一账号，2=全服；例如limit_type=0，limit_class=0，则表示每一个角色每天最多只能购买limit_max次，limit_type=1，limit_class=1，表示同一个账号* 在一周内最多只能购买limit_max次本礼包，limit_type=1，limit_class=2，则表示全服所有账号和角色加起来最多只能买limit_max个此礼包
     */
    $where = '';
    if($limit_type === 0)
    {
        $where = "DATEDIFF(DAY, FLD_DATE_REG, GETDATE())=0";
    }elseif($limit_type === 1)
    {
        $where = "DATEDIFF(WEEK, FLD_DATE_REG, GETDATE())=0";
    }

    if($limit_class === 0)
    {
        $where .= ($where != '' ? ' AND ' : '') . "FLD_CHAR_ID=" . $char_id;
    }elseif($limit_class === 1)
    {
        $where .= ($where != '' ? ' AND ' : '') . "FLD_USER_NAME='" . $username . "'";
    }


    $sql = "SELECT COUNT(*) AS FLD_COUNT FROM create_gamedb_pc.WebMall.TBL_MallPacketHistory WHERE FLD_PACKET_SERIAL=" . $packet_serial . ($where != '' ? ' AND ' . $where : '') . ";";
    
    $count = 0;
    if($sqlserver->run($sql))
    {
        $row = $sqlserver->next();
        $count = (int)$row['FLD_COUNT'];
    }
    if(is_numeric($total_num))
    {
        $count = $total_num - $count;
    }
    return $count;
}

/**
 * 取用户充值的点券数量
 *
 * @param [type] $sqlserver
 * @param [type] $type  0：取每日，1：取每周，2：累计（此时date_start和date_end有效，均为时间戳格式，某一为null则以某一开始或结束）
 * @param [type] $username
 * @return void
 */
function GetUserRechargeCash($sqlserver, $type, $username, $date_start = null, $date_end = null)
{
    $where = '';
    if($type === 0)
    {
        $where = "DATEDIFF(DAY, FLD_DATE_REG, GETDATE())=0";
    }elseif($type === 1)
    {
        $where = "DATEDIFF(WEEK, FLD_DATE_REG, GETDATE())=0";
    }elseif($type === 2)
    {
        if($date_start !== null)
        {
            $where = "FLD_DATE_REG>'" . date('Y-m-d H:i:s', $date_start) . "'";
        }
        if($date_end !== null)
        {
            $where = ($where != '' ? "(" . $where . " AND FLD_DATE_REG<'" . date('Y-m-d H:i:s', $date_end) . "')" : '');
        }
    }else{
        return 0;
    }
    $sql = "SELECT SUM(FLD_CASH) AS FLD_CASH FROM create_gamedb_pc.WebMall.TBL_MallRechargeHistory WHERE FLD_USER_NAME='" . $username . "' AND FLD_STATE='SUCCESS'" . ($where != '' ? ' AND ' . $where : '') . ";";
    $cash = 0;
    if($sqlserver->run($sql))
    {
        $row = $sqlserver->next();
        $cash = (int)$row['FLD_CASH'];
    }
    return $cash;
}

function AddPacketHistory($sqlserver, $username, $char_id, $item)
{
    $sql = "INSERT INTO create_gamedb_pc.WebMall.TBL_MallPacketHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_PACKET_TYPE, FLD_PACKET_SERIAL, FLD_DATE_REG, FLD_IP) VALUES ('" . $username . "', " . $char_id . ", " . $item['type'] . ", " . $item['serial'] . ", GETDATE(), '" . get_real_ip() . "');";
    $sqlserver->run($sql);
}

/**
 * 获取激活码使用的次数
 *
 * @param [type] $sqlserver
 * @param [type] $type 
 * 0表示激活码是一次性的（全服只能用一次）；
 * 1表示一个角色只能用一次（同账号下的其他角色仍然可以继续用，但不同的账号仍然可以继续使用）；
 * 2表示一个账号只能用一次（同账号下一个角色用了其他角色不能继续用，但不同的账号仍然可以继续使用）；
 * 3表示无限次使用（一个角色可以无限次的使用此激活码，同账号下可以不限制角色）；
 * 4表示无限次使用（同账号下只有一个角色能使用）
 * @param [type] $active_code
 * @param [type] $username
 * @param [type] $char_id
 * @return void
 */
function GetMallActiveCodeUseCount($sqlserver, $type, $active_code, $username, $char_id)
{
    $where = '';
    if($type === 0)
    {
        $where = "";
    }elseif($type === 1)
    {
        $where = "FLD_CHAR_ID=" . $char_id;
    }elseif($type === 2)
    {
        $where = "FLD_USER_NAME='" . $username . "'";
    }elseif($type === 3)
    {
        $where = "FLD_CHAR_ID=" . $char_id;
    }elseif($type === 4)
    {
        $where = "FLD_USER_NAME='" . $username . "'";
    }else{
        return null;
    }
    $sql = "SELECT COUNT(*) AS FLD_COUNT FROM create_gamedb_pc.WebMall.TBL_MallActiveCodeHistory WHERE FLD_ACTIVE_CODE='" . $active_code . "'" . ($where != '' ? ' AND ' . $where : '') . ";";
    $count = 0;
    if($sqlserver->run($sql))
    {
        $row = $sqlserver->next();
        $count = (int)$row['FLD_COUNT'];
    }
    return $count;
}

function AddMallActiveCodeHistory($sqlserver, $active_code, $username, $char_id)
{
    $sql = "INSERT INTO create_gamedb_pc.WebMall.TBL_MallActiveCodeHistory (FLD_USER_NAME, FLD_CHAR_ID, FLD_ACTIVE_CODE, FLD_DATE_REG, FLD_IP) VALUES ('" . $username . "', " . $char_id . ", '" . $active_code . "', GETDATE(), '" . get_real_ip() . "');";
    $sqlserver->run($sql);
}

/**
 * 检查激活码是否在冷却期间，返回大于零表示在冷却期
 *
 * @param [type] $sqlserver
 * @param [type] $active_code
 * @return void|int
 */
function GetMallActiveCodeCollingTime($sqlserver,$active_code, $times, $type, $username = null, $char_id = null)
{
    $where = '';
    if($type === 0)
    {
        $where = "";
    }elseif($type === 1)
    {
        $where = "FLD_CHAR_ID=" . $char_id;
    }elseif($type === 2)
    {
        $where = "FLD_USER_NAME='" . $username . "'";
    }elseif($type === 3)
    {
        $where = "FLD_CHAR_ID=" . $char_id;
    }elseif($type === 4)
    {
        $where = "FLD_USER_NAME='" . $username . "'";
    }else{
        return null;
    }
    $sql = "SELECT TOP(1) * FROM create_gamedb_pc.WebMall.TBL_MallActiveCodeHistory WHERE FLD_ACTIVE_CODE='" . $active_code . "' AND FLD_DATE_REG > DATEADD(SECOND, -" . $times . ", GETDATE())" . ($where != '' ? ' AND ' . $where : '') . " ORDER BY FLD_DATE_REG DESC;";
    $count = 0;
    if($sqlserver->run($sql))
    {
        $row = $sqlserver->next();
        if($row['FLD_ACTIVE_CODE'] == $active_code)
        {
            $count = time() - strtotime($row['FLD_DATE_REG']);
            $count = ($count < 0 ? 0 : $count);
        }
    }
    return $count;
}

function SendGameCurrency($sqlserver, $char_id, $goods){
    $sql = "EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail 
            " . $char_id . "
            ,0
            ,0
            ,N'系统'
            ,253
            ,1
            ,N'WEB商城'
            ,N'[]'
            ," . (int)$goods['Gold'] . "
            ," . (int)$goods['Starseed'] . "
            ," . (int)$goods['Relic_Fragments'] . "
            ," . (int)$goods['Relic_Core'] . "
            ," . (int)$goods['Crystal'] . "
            ," . (int)$goods['Seal'] . "
            ," . (int)$goods['Mint'] . "
            ," . (int)$goods['Brand'] . "
            ," . (int)$goods['BattleCoin'] . "
            ," . (int)$goods['RevivalCoin'] . "
            ," . (int)$goods['NormalKey'] . "
            ," . (int)$goods['EliteKey'] . "
            ," . (int)$goods['ValorsTalent'] . "
            ," . (int)$goods['Exp'] . "
            ," . (int)$goods['ReputationCid'] . "
            ," . (int)$goods['ReputationValue'] . "
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
    
    if($sqlserver->run($sql)) return true;
}

function SendGameItem($sqlserver, $char_id, $goods)
{
    $send_arr = [];
        $tmp_arr = [];
        for($i = 0; $i < count($goods); $i++)
        {
            if($i != 0 && $i % 4 == 0)
            {
                $send_arr[] = $tmp_arr;
                $tmp_arr = [];
            }
            $tmp_arr[] = $goods[$i];
            
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
                " . $char_id . "
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
            if(!$sqlserver->run($sql)) return false;
        }
}