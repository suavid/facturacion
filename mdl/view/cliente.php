<?php

class clienteView {

    private function load_settings() {
        import('scripts.periodos');
        $pf = "";
        $pa = "";
        list($pf, $pa) = cargar_periodos();
        page()->addEstigma("periodo_fiscal", $pf);
        page()->addEstigma("periodo_actual", $pa);
        page()->addEstigma("fecha_sistema", date('d/m/Y'));
    }

    public function mostrar_opciones($user) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/cliente.html');
        $this->load_settings();
        page()->setTitle('Clientes');
        page()->addEstigma('TITULO', 'Clientes');
        page()->addEstigma('back_url', '/nymsa/modulo/destruirSesion/cliente');
        page()->addEstigma('username', $user);
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function referencias($user, $cliente, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/referencias.html');
        $this->load_settings();
        page()->setTitle('Referencias del cliente');
        page()->addEstigma('TITULO', 'Referencias del cliente');
        page()->addEstigma('back_url', '/nymsa/cliente/ficha');
        page()->addEstigma('username', $user);
        page()->addEstigma('referencias', array('SQL', $cache[0]));
        page()->addEstigma('id_cliente', $cliente);
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function dato_credito($user, $cliente, $documentos) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/dato_credito.html');
        $this->load_settings();
        page()->setTitle('Datos crediticios');
        page()->addEstigma('TITULO', 'Datos crediticios');
        page()->addEstigma('back_url', '/nymsa/cliente/ficha');
        page()->addEstigma('username', $user);
        page()->addEstigma('id_cliente', $cliente);
        page()->addEstigma('APP_PATH', APP_PATH);
        page()->addEstigma('listado_documentos', array('SQL', $documentos));
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function fichaCLiente($cliente, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/resumen.html');
        $this->load_settings();
        page()->setTitle("Cliente [$cliente]");
        page()->addEstigma('TITULO', "Cliente [$cliente]");
        page()->addEstigma('resumen', array('SQL', $cache[0]));
        page()->addEstigma('back_url', '/nymsa/cliente/listado');
        page()->addEstigma('username', Session::singleton()->getUser());
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function ficha($user, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/ficha.html');
        $this->load_settings();
        page()->setTitle('Ficha cliente');
        page()->addEstigma('TITULO', 'Ficha');
        page()->addEstigma('back_url', '/nymsa/cliente/principal');
        page()->addEstigma('username', $user);
        page()->addEstigma('departamento', array('SQL',$cache['departamentos']));
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function gestion_empleados($user, $cache) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/ficha_empleado.html');
        $this->load_settings();
        page()->setTitle('Ficha cliente');
        page()->addEstigma('TITULO', 'Ficha');
        page()->addEstigma('back_url', '/nymsa/cliente/principal');
        page()->addEstigma('username', $user);
        page()->addEstigma('departamento', array('SQL',$cache['departamentos']));
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function mantenimiento($usuario) {
        template()->buildFromTemplates('template_table.html');
        page()->setTitle('Mantenimiento de clientes');
        $this->load_settings();
        page()->addEstigma("username", $usuario);
        page()->addEstigma("back_url", '/nymsa/cliente/principal');
        page()->addEstigma("TITULO", 'Clientes');
        template()->addTemplateBit('content', 'cliente/mantenimiento.html');
        template()->parseOutput();
        template()->parseExtras();
        print page()->getContent();
    }

    public function nuevo_empleado($cliente, $user) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/nuevo_empleado.html');
        $this->load_settings();
        page()->setTitle('Empleados');
        page()->addEstigma('username', $user);
        page()->addEstigma('TITULO', 'Nuevo empleado');
        page()->addEstigma('cliente', $cliente);
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function empleados($cache, $user, $pag, $fil) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/empleado.html');
        $this->load_settings();
        page()->setTitle('Empleados');
        page()->addEstigma('TITULO', "Empleados");
        page()->addEstigma('username', $user);
        page()->addEstigma('paginacion', $pag);
        page()->addEstigma('back_url', '/nymsa/cliente/principal');
        page()->addEstigma('empleados', array('SQL', $cache[0]));
        page()->addEstigma('hidden', $cache[1]);
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function listado($cache, $paginacion_str, $fil, $user) {
        template()->buildFromTemplates('template_nofixed.html');
        template()->addTemplateBit('content', 'cliente/listado_cliente.html');
        $this->load_settings();
        page()->setTitle('Clientes');
        page()->addEstigma('TITULO', "Clientes");
        page()->addEstigma('paginacion', $paginacion_str);
        page()->addEstigma('acceso', Session::getLevel());
        page()->addEstigma('username', $user);
        page()->addEstigma('back_url', '/nymsa/cliente/principal');
        page()->addEstigma('clientes', array('SQL', $cache[0]));
        while ($res = data_model()->resultsFromCache($cache[1])) {
            page()->addEstigma('ck_codigo_' . $res['id_datos'], 'checked="checked"');
        }
        page()->addEstigma('hidden', $cache[2]);
        template()->parseExtras();
        template()->parseOutput();
        print page()->getContent();
    }

    public function dato_laboral(){
        
    }

}

?>