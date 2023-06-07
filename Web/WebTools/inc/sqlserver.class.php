<?php

class SqlServer
{
    protected $conn;
    protected $ret;

    function __construct($cfgArr)
    {
        $cStr = 'sqlsrv:Server=' . $cfgArr['HOST'] . ';Database=' . $cfgArr['BASE'];
        $this->conn = new PDO($cStr, $cfgArr['USER'], $cfgArr['PASS']);
        return $this->conn;
    }

    function __destruct()
    {
        $this->free();   
        if($this->conn)
        {
            $this->conn=null;
        }
    }

    function run($sql)
    {
        if($this->conn)
        {
            $this->ret = $this->conn->query($sql);
            return $this->ret;
        }
    }

    function next(){
        if($this->ret)
        {
            return $this->ret->fetch();
        }
    }

    function free()
    {
        $this->ret = null;
    }

    function error()
    {
        if(($arr = $this->conn->error()) != null){
            $error = '';
            foreach($arr as $err){
                $error .= 'State:' . $err['SQLSTATE'] . "\r\n" . "Error:" . $err['code'] . "\r\n" . "Message:" . $err['message'];
            }
        }
        return $error;
    }

}