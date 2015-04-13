<?php

/**
 *  This class provides methods to manage user sessions
 *
 */
class Session {

    /** Handler instance @type Object  */
    private static $instance;

    /**
     * class constructor
     *
     */
    private function __construct() {
        
    }

    /**
     * gets Session instance
     *
     * @return Object
     *
     */
    public static function singleton() {

        if (!isset(self::$instance)):
            $obj = __CLASS__;
            self::$instance = new $obj;
        endif;
        return self::$instance;
    }

    /**
     * check if session ins active
     *
     * @return boolean
     *
     */
    public static function ValidateSession() {
        if (!isset($_SESSION[MODULE.'_user']) || !isset($_SESSION['level'])):
            return false;
        else:
            return true;
        endif;
    }

    public static function getUser() {
        if (self::ValidateSession()) {
            return $_SESSION[MODULE.'_user'];
        }
    }

    public static function getLevel() {
        if (self::ValidateSession()) {
            return $_SESSION['level'];
        }
    }

    /**
     * create new session
     *
     * @param string $user user name
     * @param int $level acces level
     *
     */
    public static function NewSession($user, $level) {

        $_SESSION[MODULE.'_user'] = $user;
        $_SESSION['level'] = $level;
    }

    /**
     * close current session
     *
     */
    public static function logOut() {

        $_SESSION = array();
        session_destroy();
        $parametros_cookies = session_get_cookie_params();
        setcookie(session_name(), 0, 1, $parametros_cookies["path"]);
        HttpHandler::redirect(DEFAULT_DIR);
    }

}

?>