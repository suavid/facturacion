<?php

class ErrorController {

    public function not_found() {
        proveedor_activo();
        BM::singleton()->getObject('temp')->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'error.html');
        BM::singleton()->getObject('temp')->getPage()->setTitle("Recurso no encontrado");
        BM::singleton()->getObject('temp')->getPage()->addEstigma("username", Session::singleton()->getUser());
        BM::singleton()->getObject('temp')->parseExtras();
        BM::singleton()->getObject('temp')->parseOutput();
        print BM::singleton()->getObject('temp')->getPage()->getContent();
    }

    public function e403() {
        BM::singleton()->getObject('temp')->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'e403.html');
        BM::singleton()->getObject('temp')->getPage()->setTitle("Acceso restringido");
        BM::singleton()->getObject('temp')->getPage()->addEstigma("username", Session::singleton()->getUser());
        BM::singleton()->getObject('temp')->parseExtras();
        BM::singleton()->getObject('temp')->parseOutput();
        print BM::singleton()->getObject('temp')->getPage()->getContent();
    }

    public function notificaciones() {
        $query = "SELECT * FROM alerta WHERE estado = 0 LIMIT 1";
        data_model()->executeQuery($query);
        $res = data_model()->getResult()->fetch_assoc();
        $id = $res['id'];
        $query = "UPDATE alerta SET estado = 1 WHERE id=$id";
        data_model()->executeQuery($query);
        echo json_encode($res);
    }

    public function bloquear() {
        bloqueo_pantalla();
    }

}

?>