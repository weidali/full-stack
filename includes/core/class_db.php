<?php

class DB
{

    private static $db;

    public static function connect()
    {
        if (!DB::$db) {
            if (!in_array(DB_CONNECTION, PDO::getAvailableDrivers())) {
                echo "Error: Driver for db connection is not define\n";
                die();
            }

            $db_connection = DB_CONNECTION;
            $host = DB_HOST;
            $dbname = DB_NAME;
            $port = DB_PORT;
            $dsn = "$db_connection:host=$host;dbname=$dbname;port=$port;";

            try {
                DB::$db = new PDO(
                    $dsn,
                    DB_USER,
                    DB_PASS,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]
                );
                dump("OK");
            } catch (PDOException $e) {
                print 'Error!: ' . $e->getMessage() . '<br/>';
                die();
            }
        }
        return DB::$db;
    }

    public static function query($q)
    {
        return DB::connect()->query($q);
    }

    public static function fetch_row($q)
    {
        return $q->fetch();
    }

    public static function error()
    {
        $res = DB::connect()->errorInfo();
        trigger_error($res[2], E_USER_WARNING);
        return $res[2];
    }
}
