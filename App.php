<?php require_once 'Lib.php';

class Subscriber extends DBModel
{
    public static $table="subscribers";

    public $phone;

    public function send($msg)
    {
        SMS::send($this->phone, $msg);
    }

    public function insert()
    {
        DB::primary();
        $res = DB::query('INSERT INTO subscribers (phone) VALUES (?)',[$this->phone]);
    }

    public function update()
    {

    }

    public function subscribe($company)
    {
        $sub = new Subscription;
        $sub->phone = $this->phone;
        $sub->company = $company;
        $sub->automatic = true;
        $sub->insert();
    }

    public function getStocks()
    {
        return Subscription::stocks($this);
    }

    public function save()
    {
        DB::primary();
        $res = DB::query('SELECT * FROM subscribers WHERE phone=? LIMIT 1',[$this->phone]);
        if($res->fetch()){
            $this->update();
        }
        else {
            $this->insert();
        }
        return $this;

    }

    public function delete()
    {
        DB::primary();
        $res = DB::query('DELETE FROM subscribers WHERE phone=?', [$this->phone]);
    }

    public static function findAll()
    {
        DB::primary();
        return parent::findAll();
    }
}

class Subscription extends DBModel
{
    public static $table="subscriptions";

    public $id;
    public $company;
    public $phone;
    public $automatic = true;

    public function insert()
    {
        DB::query("INSERT INTO ".static::$table." (company, phone, automatic) VALUES (?,?,?)",
            [$this->company, $this->phone, $this->automatic]);
        $this->id = DB::db()->lastInsertId();
    }

    public function update()
    {
        DB::query("UPDATE ".static::$table." SET automatic=? WHERE id=?",[$this->automatic, $this->id]);
    }

    public function getSubscriber()
    {
        return Subscriber::findBy('phone', $this->phone);
    }

    public function getStock()
    {
        return Stock::findBy('company', $this->company);
    }

    public static function subscribers($stock)
    {
        $company = is_object($stock)? $stock->company : $stock;
        return Subscriber::select("phone in (SELECT phone FROM ".static::$table." WHERE company=?)", [$company]);
    }

    public static function stocks($subscriber)
    {
        $phone = is_object($subscriber)? $subscriber->phone : $subscriber;
        return Stock::select("company in (SELECT company FROM".static::$table." WHERE phone=?)",[$phone]);
    }


}

class Stock extends DBModel
{

    public static $table="stocks";

    public $company;
    public $price;

    public function getSubscribers()
    {
        return Subscription::subscribers($this);
    }

    public static function find($company)
    {
        DB::primary();
        $res = DB::query("SELECT * FROM stocks WHERE company=?",[$company]);
        if($row = $res->fetch()){
            return static::fromRow($row);
        }
        return null;
    }

    public function insert()
    {
        DB::primary();
        $res = DB::query('INSERT INTO stocks (company,price) VALUES(?,?)',[
            $this->company, $this->price
            ]);
        return $this;
    }

    public function update()
    {

        DB::primary();
        $res = DB::query('UPDATE stocks SET company=?, price=? WHERE company=?',[
            $this->company, $this->price, $this->company
            ]);
        return $this;
    }

    public function save()
    {
        DB::primary();
        $res = DB::query('SELECT * FROM stocks WHERE company=? LIMIT 1',[$this->company]);
        if($res->fetch()){
            $this->update();
        }
        else {
            $this->insert();
        }
        return $this;
    }

    public function delete()
    {
        DB::primary();
        DB::query('DELETE FROM stocks WHERE company=?',[$this->company]);
    }

    public static function findAll()
    {
        DB::primary();
        return parent::findAll();
    }
}

class SMS
{

    private static $dbConnected = false;

    /**
     * send sms using HTTP API, returns XML response
     */
    public static function send($to, $msg)
    {
        $url = Config::sms('url');
        $url .= "?".implode("&",
            "action=".Config::sms('action'),
            "username=".Config::sms('user'),
            "password=".Config::sms('pass'),
            "messagetype=".Config::sms('type'),
            "recipient=".Config::sms($to),
            "messagedata=".urlencode($msg)
        );
        return file_get_contents($url);

    }


    //the following functions post or  fetch messages to ozekisms database

    public static function connectDB()
    {
        if(!self::$dbConnected){
            $db = new PDO(Config::smsdb('connstr'), Config::smsdb('user'), Config::smsdb('pass'));
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            DB::add('sms', $db);
            self::$dbConnected = true;
        }
    }

    public static function sendDB($to, $msg)
    {
        SMS::connectDB();
        DB::set('sms');
        DB::query('INSERT INTO ozekimessageout (receiver, msg, status,msgtype) VALUES (?,?,?,?)',
            [$to, $msg, 'send','SMS:TEXT']);
    }

    public static function fetchDB()
    {
        SMS::connectDB();
        DB::set('sms');
        $res = DB::query('SELECT id, sender, senttime, msg FROM ozekimessagein ORDER BY senttime DESC');
        $msgs = [];
        while($row = $res->fetch()){
            $msgs[] = (object) ['id'=>$row['id'],'message'=>$row['msg'],'time'=>$row['senttime'],'sender'=>$row['sender']];
        }
        return $msgs;
    }

    public static function fetchLaterThan($time)
    {
        SMS::connectDB();
        DB::set('sms');
        $res = DB::query('SELECT sender, senttime, msg FROM ozekimessagein WHERE senttime > ? ORDER BY senttime DESC',
            [$time]);
            $msgs = [];
        while($row = $res->fetch()){
            $msgs[] = (object) ['id'=>$row['id'],'message'=>$row['msg'],'time'=>$row['senttime'],'sender'=>['sender']];
        }
        return $msgs;
    }

    public static function delete($sms)
    {
        SMS::connectDB();
        DB::set('sms');
        $res = DB::query("DELETE FROM ozekimessagein WHERE id=?",[$sms->id]);
    }

}
