<?php


namespace QscmfApi\Session;

class Session extends ASession
{
    public $name = \QscmfApi\CusSession::SESSION_TYPE_COMMON;

    public static function set($key, $value, $expire = null)
    {
        session($key, $value);
    }

    public static function get($key =''){
        return session($key);
    }
    
    public static function setId($sid = '')
    {
        $sid && session_id($sid);
        self::$sid = session_id();
        return self::$sid;
    }

}