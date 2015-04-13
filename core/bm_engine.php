<?php

/**
 * This class provides methods of union between core modules 
 */
class BM {

    /**  stored core modules @type \ArrayObject $objects  */
    private static $objects = array();

    /** stored application settings @type \ArrayObject $settings */
    private static $settings = array();

    /** core name @type string $name */
    private static $name = 'Business manager 1.0';

    /** core instance @type \BMObject $instance */
    private static $instance;

    /**
     * class constructor
     * 
     * @return none
     * 
     */
    private function __construct() {
        
    }

    /**
     * gets core instance
     * 
     * @return BMObject 
     * 
     */
    public static function singleton() {
        if (!isset(self::$instance)) {
            $obj = __CLASS__;
            self::$instance = new $obj;
        }
        return self::$instance;
    }

    /**
     * avoid object clonation 
     * 
     * @return none
     * 
     */
    public function __clone() {
        trigger_error('ONLY ONE INSTANCE ALLOWED.', E_USER_ERROR);
    }

    /**
     * load modules
     * 
     * @param string $object module name
     * @param string $key module reference
     * @return none
     * 
     */
    public static function storeObject($object, $key) {
        self::$objects[$key] = new $object(self::$instance);
    }

    /**
     * bring access to loaded modules
     * 
     * @param string $key module reference
     * @return module instance
     * 
     */
    public static function getObject($key) {
        if (is_object(self::$objects[$key])) {
            return self::$objects[$key];
        }
    }

    /**
     * store new setting
     * 
     * @param string $data this is the new setting value
     * @param string $key this is the new setting access keyword
     * @return none
     * 
     */
    public static function storeSetting($data, $key) {
        self::$settings[$key] = $data;
    }

    /**
     * get settings
     * 
     * @param string $key setting access keyword
     * @return string 
     * 
     */
    public static function getSetting($key) {
        return self::$settings[$key];
    }

    /**
     * get engine name
     * 
     * @return string
     * 
     */
    public static function getName() {
        return self::$name;
    }

}

?>