<?php

class moduloView {

    private function load_settings() {
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();
        page()->addEstigma("periodo_fiscal", $pf);
        page()->addEstigma("periodo_actual", $pa);
        page()->addEstigma("fecha_sistema", date('d/m/Y'));
    }

    public function listar($usuario, $nombreEmpresa, $direccionEmpresa, $telefonoEmpresa, $faxEmpresa) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Modulos');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("nombre_empresa", $nombreEmpresa);
        page()->addEstigma("direccion", $direccionEmpresa);
        page()->addEstigma("telefono", $telefonoEmpresa);
        page()->addEstigma("fax", $faxEmpresa);
        if(Session::singleton()->getLevel()==1){
            page()->addEstigma("opciones_configuracion", '<a href="/nymsa/modulo/opcionesDeSistema" class="button primary"><i class="icon-cog"></i> Configuración</a>');
        }else{
            page()->addEstigma("opciones_configuracion","");
        }
        page()->addEstigma("back_url", '/nymsa/logout/user');
        page()->addEstigma("TITULO", 'Modulos');
        template()->addTemplateBit('content', 'modulos.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function cuenta($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Cuenta de usuario');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        template()->addTemplateBit('content', 'configuracion.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function alerta() {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Cuenta de usuario');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        template()->addTemplateBit('content', 'configuracion_alerta.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function inboxRead($inbox, $tipo) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Bandeja de entrada');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        page()->addEstigma("titulo", $inbox->titulo);
        page()->addEstigma("id", $inbox->id);
        page()->addEstigma("tipo", $tipo);
        if($inbox->leido_a_las=="0000-00-00 00:00:00"){
            page()->addEstigma("leido_a_las", "No ha sido leído todavía");
        }else{
            page()->addEstigma("leido_a_las", $inbox->leido_a_las);
        }
        page()->addEstigma("destinatario", $inbox->destinatario);
        page()->addEstigma("remitente", $inbox->remitente);
        page()->addEstigma("fecha", $inbox->fecha);
        page()->addEstigma("mensaje", $inbox->mensaje);
        template()->addTemplateBit('content', 'inbox.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function inboxReadOut($inbox) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Bandeja de salida');
        page()->addEstigma("username", Session::singleton()->getUser());
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        page()->addEstigma("id", $inbox->id);
        page()->addEstigma("titulo", $inbox->titulo);
        page()->addEstigma("destinatario", $inbox->destinatario);
        if($inbox->leido_a_las=="0000-00-00 00:00:00"){
            page()->addEstigma("leido_a_las", "No ha sido leído todavía");
        }else{
            page()->addEstigma("leido_a_las", $inbox->leido_a_las);
        }
        page()->addEstigma("mensaje", $inbox->mensaje);
        template()->addTemplateBit('content', 'inboxOut.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function opcionesDeSistema($usuario, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Opciones de sistema');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        page()->addEstigma("fecha", date("d/m/Y"));
        page()->addEstigma("system_settings", array('SQL', $cache[0]));

        template()->addTemplateBit('content', 'opciones.html');
        template()->parseOutput();
        template()->parseExtras();

        print page()->getContent();
    }

    public function politicas_de_actualizacion($usuario) {
        template()->buildFromTemplates('template_nofixed.html');
        $this->load_settings();
        page()->setTitle('Politicas de actualizacion');
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/nymsa/modulo/listar');
        page()->addEstigma("TITULO", 'Modulos');
        page()->addEstigma("fecha", date("d/m/Y"));
        template()->addTemplateBit('content', 'politicas_de_actualizacion.html');
        template()->parseOutput();
        template()->parseExtras();

        print page()->getContent();
    }

}

?>