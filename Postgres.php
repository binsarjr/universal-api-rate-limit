<?php

namespace ApiRateLimit\Driver;

class Postgres
{
    public static $connected = false;
    public $conected;
    public $con;
    public $sql;
    public $error;
    public $host;
    public $user;
    public $password;
    public $database;
    public $config;
    public $secur;

    public function __construct($host = '', $user = '', $password = '', $database = '', $port = '', $timeout = '')
    {
        $host = $host ?: DB_HOST;
        $user = $user ?: DB_USER;
        $password = $password ?: DB_PASS;
        $database = $database ?: DB_NAME;
        $port = $port ?: DB_PORT;
        $connection = "host={$host} port={$port} dbname={$database} user={$user} password= {$password}";

        if ($timeout) {
            $connection .= ' connect_timeout='.$timeout;
        }

        if ($this->con = pg_connect($connection)) {
            $this->connected = true;
            Postgres::$connected = true;
        }

        /*
         * backup
         */

        // $host 		= ($this->host=='')? DB_HOST:$this->host;
        // $user 		= ($this->user=='')? DB_USER:$this->user;
        // $password 	= ($this->password=='')? DB_PASS:$this->password;
        // $database 	= ($this->database=='')? DB_NAME:$this->database;
        // $port 		= DB_PORT;
        // $connection = "host=$host port=$port dbname=$database user=$user password= $password";
        // if ($this->con = pg_connect($connection)){
        // 	$this->connected = true;
        // }
    }

    /*
    * contoh penggunaan prepared query
    * query("select * from pemda where kodepemda='$1' and tahun=$2',
    *		array(3200,2019)
    *	);
    */
    public function query($qry, $array = [])
    {
        $this->sql = $qry;
        if (empty($array)) {
            $result = pg_query($this->con, $qry);

            if ($result) {
                $error = '';
            } else {
                $error = 'ERROR:';
            }

            return $result;
        }
        $logqry = $qry;
        foreach ($array as $key => $val) {
            $keyname = '$'.($key + 1);
            $logqry = str_replace($keyname, $val, $logqry);
        }

        $result = pg_prepare($this->con, '', $qry);
        $result = pg_execute($this->con, '', $array);

        if ($result) {
            $error = '';
        } else {
            $error = 'ERROR:';
        }

        return $result;
    }

    public function fetch($result, $type = 'object')
    {
        if ('object' == $type) {
            return pg_fetch_object($result);
        }

        return pg_fetch_assoc($result);
    }

    public function lastError()
    {
        $error = addslashes(str_replace("\n", ' ', pg_last_error($this->con)));
        $arrerror = explode('CONTEXT', $error);
        // die(pg_last_error($this->con));
        return @$arrerror[0];
    }

    public function escape_string($str)
    {
        return pg_escape_string($this->con, $str);
    }

    public function first(&$res)
    {
        pg_result_seek($res, 0);
    }

    public function fetchArray($qry)
    {
        return @pg_fetch_array($qry);
    }

    public function fetchAssoc($qry)
    {
        return @pg_fetch_assoc($qry);
    }

    public function fetchAssoc_Query($qry)
    {
        return $this->fetchAssoc($this->query($qry));
    }

    public function fetchObject($qry)
    {
        return @pg_fetch_object($qry);
    }

    public function numRows($qry)
    {
        return @pg_num_rows($qry);
    }

    public function numField($qry)
    {
        return @pg_num_fields($qry);
    }

    public function getData($table)
    {
        return $this->query('SELECT * FROM '.$table);
    }

    public function getDataWhere($table, $wfield, $wvalue)
    {
        return $this->query('SELECT * FROM '.$table.' WHERE  '.$wfield." = '".$wvalue."'");
    }

    public function affectedRow($res)
    {
        return pg_affected_rows($res);
    }
}