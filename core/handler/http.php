<?php

/**
 * This class provides basic operations for application uri
 *
 */
class HttpHandler {

    /**
     * change current uri
     * 
     * @param string $uri target location
     *
     */
    public static function redirect($uri) {
        header("location:{$uri}");
    }

}

?>