<?php

/**
 * This class provides operations to creates model and view with given controller
 *
 */
class Helper {

    /**
     * creates new model 
     *
     * @param Object $obj ControllerObject
     *
     * @return ModelObject 
     *
     */
    public static function get_model($obj = NULL) {
        $model = str_replace('Controller', 'Model', get_class($obj));
        return new $model();
    }

    /**
     * creates new view 
     *
     * @param Object $obj ControllerObject
     *
     * @return ViewObject 
     *
     */
    public static function get_view($obj = NULL) {
        $view = str_replace('Controller', 'View', get_class($obj));
        return new $view();
    }

}

?>