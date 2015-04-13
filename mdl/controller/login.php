<?php

import('mdl.view.login');
import('mdl.model.login');
proveedor_activo();

class LoginController extends controller {

    public function form() {
        if (!Session::singleton()->ValidateSession()) {
            $this->view->show_form();
        } else {
            HttpHandler::redirect('/'.MODULE.'/factura/principal');
        }
    }

    public function info() {
        $this->view->show_info();
    }

    public function login() {
        if (empty($_POST)) {
            HttpHandler::redirect('/'+MODULE+'/login/form');
        } else {
            BM::singleton()->getObject('db')->newConnection(HOST, USER, PASSWORD, DATABASE);
            $usuario = BM::singleton()->getObject('db')->sanitizeData($_POST['usuario']);
            $clave = cifrar_RIJNDAEL_256($_POST['clave']);
            $query = "SELECT * FROM empleado WHERE usuario='{$usuario}' AND clave='{$clave}';";
            BM::singleton()->getObject('db')->executeQuery($query);
            if (BM::singleton()->getObject('db')->getNumRows() > 0) {
                $level = 1;
                while ($data = BM::singleton()->getObject('db')->getResult()->fetch_assoc()) {
                    $level = $data['permiso'];
                }
                Session::singleton()->NewSession($usuario, $level);
                HttpHandler::redirect('/'.MODULE.'/login/form');
            } else {
                HttpHandler::redirect('/'.MODULE.'/login/form?error_id=2');
            }
        }
    }

}

?>
