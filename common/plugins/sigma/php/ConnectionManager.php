<?php

class ConManager {

	private static $conexion;

	public function __construct(){

		
	}

    public static function getConnection() {

    	if(!isset(self::$conexion)){
    		//change to your database server/user name/password
	        self::$conexion = mysqli_connect("localhost", "root", PASSWORD) or
	                die("Could not connect: " . mysql_error());
	        //change to your database name
	        mysqli_select_db(self::$conexion, DATABASE) or
	                die("Could not select database: " . mysql_error());
    	}

        return self::$conexion;
    }

}

?>