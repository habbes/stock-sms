<?php

class DBModel
{
    
    public function delete()
    {
        DB::query("DELETE FROM " . static::$table . " WHERE id=?", $this->id);
    }

    public static function select($q, $values = [])
    {
        $res = DB::query('SELECT * FROM ' . static::$table . ' WHERE ' . $q);
        $objs = [];
        while($row = $res->fetch()){
            $objs[] = static::fromRow($row);
        }
        return $objs;
    }

    public static function findAll()
    {
        $res = DB::query('SELECT * FROM ' . static::$table);
        $objs = [];
        while($row = $res->fetch()){
            $objs[] = static::fromRow($row);
        }
        return $objs;
    }

    public static function fromRow($row)
    {
        $obj = new static();
        foreach($row as $prop => $val){
            if(property_exists($obj, $prop)){
                $obj->$prop = $val;
            }
        }
        return $obj;
    }

    public static function find($id)
    {
    	$result = DB::query('SELECT * FROM ' . static::$table . ' WHERE id=? LIMIT 1', $id);
    	if($row = $result->fetch()){
    		return static::fromRow($row);
    	}
    	return null;
    }

    public static function findBy($field, $value)
    {
    	$result = DB::query("SELECT * FROM " . static::$table . " WHERE $field=?", [$value]);
    	$objs = [];
    	while($row = $result->fetch()){
    		$objs[] = static::fromRow($row);
    	}
    	return $objs;
    }


}


class Request
{
    public static function post($var,$default = null)
    {
        if(isset($_POST[$var]))
            return $_POST[$var];
        return $default;
    }

    public static function get($var,$default = null)
    {
        if(isset($_GET[$var]))
            return $_GET[$var];
        return $default;
    }

    public static function any($var, $default = null)
    {
        if(isset($_GET[$var]))
            return $_GET[$var];
        if(isset($_POST[$var]))
            return $_POST[$var];
        return $default;
    }

    public static function type()
    {
    	return $_SERVER['REQUEST_METHOD'];
    }

    public static function isGet()
    {
    	return static::type() == 'GET';
    }

    public static function isPost()
    {
    	return static::type() == 'POST';
    }

}


class DB
{
    private static $db;
    private static $connections = array();

    private function __construct(){}

    //default DB
    private static function makePrimary()
    {

        $envdb = getenv('DATABASE_URL');
        $dbopts = parse_url($envdb);
        $connstr = $envdb? 'pgsql:dbname='.ltrim($dbopts['path'],'/').';host='.$dbopts['host'] : Config::db('connstr');
        $user = $envdb? $dbopts['user'] : Config::db('user');
        $pass = $envdb? $dbopts['pass'] : Config::db('pass');

        $db = new PDO($connstr, $user, $pass);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        static::add('primary', $db);

    }

    public static function add($name, $db)
    {
        static::$connections[$name] = $db;
    }

    public static function set($name)
    {
        if(!static::$db)
            static::makePrimary();
        static::$db = static::$connections[$name];
    }

    public static function primary()
    {
        static::set('primary');
    }

    public static function db()
    {
        if(!static::$db){
            static::makePrimary();
            static::primary();
        }
        return static::$db;
    }

    public static function query($q, $vars = array())
    {
        $stmt = self::db()->prepare($q);
        foreach($vars as $key=>$val){
            $var = is_numeric($key)? $key + 1 : $key;
            $stmt->bindValue($var, $val);
        }
        $stmt->execute();
        return $stmt;
    }
}

class Config
{
    private static $loaded = false;
    private static $data = [];

    private static function load()
    {
        if(!self::$loaded){
            if(file_exists(__DIR__."/config")){
                $lines = file(__DIR__.'/config', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach($lines as $line){
                    $line = trim($line);
                    if($line[0] == "#")
                        continue;
                    $line = $line;
                    $parts = explode("=", $line, 2);
                    $var = strtolower($parts[0]);
                    $value = $parts[1];
                    $varParts = explode("_", $var);
                    $namespace = $varParts[0];
                    $name = $varParts[1];
                    if(!isset(self::$data[$namespace])){
                        self::$data[$namespace] = [];
                    }
                    self::$data[$namespace][$name] = $value;
                }
            }
            static::$loaded = true;
        }
    }

    public static function __callStatic($func, $args)
    {
        self::load();
        if(isset(self::$data[$func])){
            if(count($args) > 0 && isset(self::$data[$func][$args[0]])){
                return self::$data[$func][$args[0]];
            }
        }
        return count($args)>= 2? $args[1] : null;
    }

}
