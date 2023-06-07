<?php
require_once dirname(__FILE__) . '/inc/common.php';
require_once dirname(__FILE__) . '/inc/sqlserver.class.php';
if(isPost())
{
    if(!isset($_POST['action'])) die_json('fail', '缺少参数！');
    switch($_POST['action'])
    {
        case 'verifyauth':
            foreach(AUTH_CODE as $code)
            {
                if($code == $_POST['authcode'])
                {
                    $_SESSION['AUTH_CODE'] =$code;
                    die_json('success', '验证通过！');
                }
            }
            die_json('fail', '授权码错误！');
        case 'quitverify':
            unset($_SESSION['AUTH_CODE']);
            die_json('success', '已撤销验证授权！');
        case 'sendmail':
            if(!$_SESSION['AUTH_CODE']) die_json('fail', '请先验证授权！');
            if(!$_POST['Owner_DB_Id']) die_json('fail', '请选择角色！');
            $sqlserver = new SqlServer(SQLSERVER_CONFIG);
            if(!$sqlserver) die_json('fail', '连接服务器失败！');
            $sql = "EXEC create_gamedb_pc.dbo.BLSP_Native_CreateMail 
            " . $_POST['Owner_DB_Id'] . "
            ,0
            ,0
            ,N'系统'
            ," . (int)$_POST['SystemMailCid'] . "
            ,1
            ,N'" . $_POST['Title'] . "'
            ,N'" . ($_POST['Content'] ? $_POST['Content'] : '[]') . "'
            ," . ($_POST['Gold'] ? (int)$_POST['Gold'] : 0) . "
            ," . ($_POST['Starseed'] ? (int)$_POST['Starseed'] : 0) . "
            ," . ($_POST['Relic_Fragments'] ? (int)$_POST['Relic_Fragments'] : 0) . "
            ," . ($_POST['Relic_Core'] ? (int)$_POST['Relic_Core'] : 0) . "
            ," . ($_POST['Crystal'] ? (int)$_POST['Crystal'] : 0) . "
            ," . ($_POST['Seal'] ? (int)$_POST['Seal'] : 0) . "
            ," . ($_POST['Mint'] ? (int)$_POST['Mint'] : 0) . "
            ," . ($_POST['Brand'] ? (int)$_POST['Brand'] : 0) . "
            ," . ($_POST['BattleCoin'] ? (int)$_POST['BattleCoin'] : 0) . "
            ," . ($_POST['RevivalCoin'] ? (int)$_POST['RevivalCoin'] : 0) . "
            ," . ($_POST['NormalKey'] ? (int)$_POST['NormalKey'] : 0) . "
            ," . ($_POST['EliteKey'] ? (int)$_POST['EliteKey'] : 0) . "
            ," . ($_POST['ValorsTalent'] ? (int)$_POST['ValorsTalent'] : 0) . "
            ," . ($_POST['Exp'] ? (int)$_POST['Exp'] : 0) . "
            ," . ($_POST['ReputationCid'] ? (int)$_POST['ReputationCid'] : 0) . "
            ," . ($_POST['ReputationValue'] ? (int)$_POST['ReputationValue'] : 0) . "
            ,0
            ," . ($_POST['Item_CId_1'] ? (int)$_POST['Item_CId_1'] : 0) . "
            ," . ($_POST['Item_CId_1'] ? (int)$_POST['Item_Amount_1'] : 0) . "
            ," . ($_POST['Item_CId_1'] ? (int)$_POST['Item_Enchant_1'] : 0) . "
            ," . ($_POST['Item_CId_1'] ? (int)$_POST['Item_Grade_1'] : 0) . "
            ,0
            ," . ($_POST['Item_CId_2'] ? (int)$_POST['Item_CId_2'] : 0) . "
            ," . ($_POST['Item_CId_2'] ? (int)$_POST['Item_Amount_2'] : 0) . "
            ," . ($_POST['Item_CId_2'] ? (int)$_POST['Item_Enchant_2'] : 0) . "
            ," . ($_POST['Item_CId_2'] ? (int)$_POST['Item_Grade_2'] : 0) . "
            ,0
            ," . ($_POST['Item_CId_3'] ? (int)$_POST['Item_CId_3'] : 0) . "
            ," . ($_POST['Item_CId_3'] ? (int)$_POST['Item_Amount_3'] : 0) . "
            ," . ($_POST['Item_CId_3'] ? (int)$_POST['Item_Enchant_3'] : 0) . "
            ," . ($_POST['Item_CId_3'] ? (int)$_POST['Item_Grade_3'] : 0) . "
            ,0
            ," . ($_POST['Item_CId_4'] ? (int)$_POST['Item_CId_4'] : 0) . "
            ," . ($_POST['Item_CId_4'] ? (int)$_POST['Item_Amount_4'] : 0) . "
            ," . ($_POST['Item_CId_4'] ? (int)$_POST['Item_Enchant_4'] : 0) . "
            ," . ($_POST['Item_CId_4'] ? (int)$_POST['Item_Grade_4'] : 0) . "
            ,N'" . date('Y-m-d H:i:s') . "'
            ,N'2079-01-01 00:00:00'
            ,NULL";
            if(!$sqlserver->run($sql)) die_json('fail', '服务器内部错误！' . $sqlserver->error());
            die_json('success', '发送成功！');
        case 'querycharacter':
            if(!$_SESSION['AUTH_CODE']) die_json('fail', '请先验证授权！');
            $sqlserver = new SqlServer(SQLSERVER_CONFIG);
            if(!$sqlserver) die_json('fail', '连接服务器失败！');
            $sql = "SELECT * FROM create_gamedb_pc.dbo.DBPlayer WHERE PLayer_Name LIKE '%" . $_POST['username'] . "%'";
            if(!$sqlserver->run($sql)) die_json('fail', '服务器错误！' . $sqlserver->error());
            $list = [];
            while($row = $sqlserver->next())
            {
                $list[] = [
                    'char_id' => $row['DB_ID'],
                    'char_name' => $row['Player_Name']
                ];
            }
            if(count($list) == 0) die_json('fail', '找不到符合的角色！');
            die_json('success', '找到符合的' . count($list) . '个角色！', $list);
        default:
            die_json('fail', '不支持的类型！');
    }

}