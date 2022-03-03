<?php

namespace App\Helpers;
use Illuminate\Support\Facades\DB;

class Util
{
    public static function open_connect_database_default()
    {
            $db_host= env("DB_HOST");
            $db_connection_aux = env("DB_CONNECTION_AUX");
            $db_connection_name_dblink =env("DB_CONNECTION_DBLINK");
            $db_port = env("DB_PORT");
            $db_database = env("DB_DATABASE");
            $db_username= env("DB_USERNAME");
            $db_password = env("DB_PASSWORD");
            $open_conection= DB::connection($db_connection_aux)->select("SELECT dblink_connect('$db_connection_name_dblink','hostaddr=$db_host port=$db_port dbname=$db_database user=$db_username password=$db_password');");
            return $open_conection;
    }

    public static function close_conection_database_default()
    {
        $db_connection_aux = env("DB_CONNECTION_AUX");
        $db_connection_name_dblink = env("DB_CONNECTION_DBLINK");
        $close_conection= DB::connection($db_connection_aux)->select("SELECT dblink_disconnect('$db_connection_name_dblink');");
        return $close_conection;
    }

    public static function open_connect_database_aux()
    {
            $db_host_aux= env("DB_HOST_AUX");
            $db_connection = env("DB_CONNECTION");
            $db_connection_name_dblink_aux =env("DB_CONNECTION_DBLINK_AUX");
            $db_port_aux = env("DB_PORT_AUX");
            $db_database_aux = env("DB_DATABASE_AUX");
            $db_username_aux= env("DB_USERNAME_AUX");
            $db_password_aux = env("DB_PASSWORD_AUX");
            $open_conection_aux= DB::connection($db_connection)->select("SELECT dblink_connect('$db_connection_name_dblink_aux','hostaddr=$db_host_aux port=$db_port_aux dbname=$db_database_aux user=$db_username_aux password=$db_password_aux');");
            return $open_conection_aux;
    }

    public static function close_conection_database_aux()
    {
        $db_connection = env("DB_CONNECTION");
        $db_connection_name_dblink_aux = env("DB_CONNECTION_DBLINK_AUX");
        $close_conection_aux= DB::connection($db_connection)->select("SELECT dblink_disconnect('$db_connection_name_dblink_aux');");
        return $close_conection_aux;
    }
}
