<?php

class SqlServer
{
    protected $conn;
    protected $ret;

    function __construct($host, $user, $pswd, $base)
    {
        $arr = array(
            "UID" => $user,
            "PWD" => $pswd,
            "DATABASE" => $base,
            "CHARACTERSET" => "UTF-8"
        );
        $this->conn = @sqlsrv_connect($host, $arr);
        return $this->conn;
    }

    function __destruct()
    {
        $this->free();   
        if($this->conn)
        {
            @sqlsrv_close($this->conn);
        }
    }

    function run($sql)
    {
        if($this->conn)
        {
            $this->ret = @sqlsrv_query($this->conn, $sql);
            return $this->ret;
        }
    }

    function next(){
        if($this->ret)
        {
            return @sqlsrv_fetch_array($this->ret);
        }
    }

    function free()
    {
        if(is_object($this->ret))
        {
            @sqlsrv_free_stmt($this->ret);
        }
        $this->ret = false;
    }

    function error()
    {
        if(($arr = sqlsrv_errors()) != null){
            $error = '';
            foreach($arr as $err){
                $error .= 'State:' . $err['SQLSTATE'] . "\r\n" . "Error:" . $err['code'] . "\r\n" . "Message:" . $err['message'];
            }
        }
        return $error;
    }

}