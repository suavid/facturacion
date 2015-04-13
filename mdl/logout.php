<?php

class LogoutController {

    public function user() {

        Session::logOut();
    }

}

?>