<?php

import('mdl.model.modulo');
import('mdl.view.modulo');

if (!Session::ValidateSession())
    HttpHandler::redirect(DEFAULT_DIR);

class moduloController extends controller {

    private $MODULES = array(
        'inventario' => array('acces' => false, 'subCategory' => array('colores' => false, 'tallas' => false)),
        'factura' => array('acces' => false, 'subCategory' => array()),
        'cliente' => array('acces' => false, 'subCategory' => array('ficha' => false, 'listado' => false))
    );

    public function listar() {
        $user = Session::singleton()->getUser();
        
        $sys = $this->model->get_child('system');
        $sys->get(1);

        $nombreEmpresa    = $sys->nombre_comercial;
        $direccionEmpresa = $sys->direccion; 
        $telefonoEmpresa  = $sys->telefono;
        $faxEmpresa       = $sys->fax; 
        $this->view->listar($user, $nombreEmpresa, $direccionEmpresa, $telefonoEmpresa, $faxEmpresa);
    }

    public function opcionesDeSistema() {

        $level = Session::singleton()->getLevel();
        if ($level == 1) {
            $user = Session::singleton()->getUser();
            $cache = array();
            $cache[0] = $this->model->get_child('system')->get_list();
            $this->view->opcionesDeSistema($user, $cache);
        } else {
            HttpHandler::redirect('/nymsa/modulo/listar?error=1530');
        }
    }

    public function politicas_de_actualizacion() {
        if (Session::singleton()->getLevel() == 1) {
            $this->view->politicas_de_actualizacion(Session::singleton()->getUser());
        } else {
            HttpHandler::redirect('/nymsa/modulo/listar?error=1530');
        }
    }

    public function actualizar_campo() {
        if (isset($_POST) && !empty($_POST)) {
            $json = json_decode(stripslashes($_POST["campos"]));
            foreach ($json as $pp) {
                $campo = $pp->{'campo'};
                $valor = ($pp->{'valor'}) ? true : 0;
                $this->model->actualizar_campo($campo, $valor);
            }
            $this->model->habilitar();
        }
    }

    public function cargar_campos() {
        $this->model->cargar_campos();
    }

    public function requiere_actualizar() {
        $response = array();
        $cliente = $this->model->get_sibling("cliente");
        if ($_POST['cliente'] != 0) {
            $cliente->get(addslashes($_POST['cliente']));
            $response['actualizar'] = ( $cliente->requiere_actualizar ) ? true : false;
        }
        echo json_encode($response);
    }

    public function efectuar_actualizacion() {
        $cliente = $this->model->get_sibling("cliente");
        $cliente->get(addslashes($_POST['cliente']));

        $json = json_decode($_POST['update']);
        foreach ($json as $row) {
            $cliente->{$row->{'campo'}} = $row->{'valor'};
        }
        $cliente->requiere_actualizar = '0';
        $cliente->save();
    }

    public function cambiar_clave() {
        $system = $this->model->get_child('system');
        $system->get(1);
        $system->set_attr('clave', md5($_POST['clave']));
        $system->save();
    }

    public function cambiar_clave_usuario() {
        $usuario = $this->model->get_child('empleado');
        $usuario->get($_POST['usuario']);
        $usuario->set_attr('clave', cifrar_RIJNDAEL_256($_POST['clave']));
        $usuario->save();
    }

    public function CheckIfModuleEnabled($moduleName) {
        if (isset($this->MODULES[$moduleName])) {
            return $this->MODULES[$moduleName]['acces'];
        } else {
            return false;
        }
    }

    public function system_save() {
        $system = $this->model->get_child('system');

        $system->get($_POST['id']);
        $system->change_status($_POST);
        $system->save();

        HttpHandler::redirect('/nymsa/modulo/opcionesDeSistema');
    }

    public function CheckIfModuleEnabledStr($moduleName) {
        $ret = array();
        $ret['Module'] = $moduleName;
        if (isset($this->MODULES[$moduleName])) {
            $ret['Result'] = $this->MODULES[$moduleName]['acces'];
        } else {
            $ret['Result'] = false;
        }
        echo json_encode($ret);
    }

    public function serializeModules() {
        return serialize($this->MODULES);
    }

    public function CheckIfSubModuleEnabled($moduleName, $subModuleName) {
        if (isset($this->MODULES[$moduleName]['subCategory'][$subModuleName])) {
            return $this->MODULES[$moduleName]['subCategory'][$subModuleName];
        } else {
            return false;
        }
    }

    public function crearSesion($nm) {
        $_SESSION[$nm] = true;
        HttpHandler::redirect('/nymsa/' . $nm . '/principal');
    }

    public function destruirSesion($nm) {
        unset($_SESSION[$nm]);
        HttpHandler::redirect('/nymsa/modulo/listar');
    }

    public function cuenta() {
        $this->view->cuenta(Session::singleton()->getUser());
    }

    public function autorizacion() {
        $clave = $_POST['clave'];
        $ret = array();
        $ret['auth'] = false;
        $system = $this->model->get_child('system');
        $system->get(1);
        if (md5($clave) == $system->get_attr('clave')) {
            $ret['auth'] = true;
        }

        echo json_encode($ret);
    }

    public function inboxPendientes(){

        $this->model->inboxPendientes();
    }

    public function inboxPreview(){
        
        $this->model->inboxPreview();
    }

    public function leerBuzon(){
        
        $this->model->leerBuzon($_POST['leidos'], $_POST['cantidad']);
    }

    public function leerBuzonSalida(){
        
        $this->model->leerBuzonSalida($_POST['leidos'], $_POST['cantidad']);
    }

    public function leerBuzonArchivados(){
        
        $this->model->leerBuzonArchivados($_POST['leidos'], $_POST['cantidad']);
    }

    public function inboxRead(){
        $inbox = $this->model->get_child('inbox');
        if(isset($_GET['inboxId'])){
            $inbox->get($_GET['inboxId']);
            $tipo = ( isset($_GET['type']) ) ? $_GET['type']: "in";
            if($tipo=="in"){
                if($inbox->exists($_GET['inboxId'])){
                    $inbox->leido = "1";
                    $inbox->leido_a_las = date("Y-m-d H:i:s");
                    $inbox->save();
                }
            }
            $this->view->inboxRead($inbox, $tipo);
        }
    }

    public function inboxReadOut(){
        $inbox = $this->model->get_child('inbox');
        if(isset($_GET['inboxId'])){
            $inbox->get($_GET['inboxId']);
            $this->view->inboxReadOut($inbox);
        }
    }

    public function enviarMensaje(){
        if(isset($_POST) && !empty($_POST)){
            $mensaje = $_POST['mensaje'];
            $destinatario = $_POST['destinatario'];
            $titulo = $_POST['titulo'];

            $inbox = $this->model->get_child('inbox');
            $inbox->mensaje = str_replace("\n", "<br/>", $mensaje);
            $inbox->destinatario = $destinatario;
            $inbox->remitente = Session::singleton()->getUser();
            $inbox->fecha = date("Y-m-d");
            $inbox->titulo = $titulo;
            $inbox->save();
            HttpHandler::redirect('/nymsa/modulo/inboxRead?inboxId=0&Compose=true');
        }
    }

    public function eliminar_mensaje(){
        if(isset($_GET['id_inbox']) && !empty($_GET['id_inbox'])){
            $id_inbox = $_GET['id_inbox'];
            $inbox = $this->model->get_child('inbox');
            if($inbox->exists($id_inbox)){
                $inbox->get($id_inbox);
                $inbox->delete($id_inbox);
            }
        }

       HttpHandler::redirect('/nymsa/modulo/inboxRead?inboxId=0&Compose=true');
    }

    public function archivar_mensaje(){
        if(isset($_GET['id_inbox']) && !empty($_GET['id_inbox'])){
            $id_inbox = $_GET['id_inbox'];
            $inbox = $this->model->get_child('inbox');
            if($inbox->exists($id_inbox)){
                $inbox->get($id_inbox);
                $inbox->archivado = "1";
                $inbox->save();
            }
        }

       HttpHandler::redirect('/nymsa/modulo/inboxRead?inboxId=0&Compose=true');
    }

    public function desarchivar_mensaje(){
        if(isset($_GET['id_inbox']) && !empty($_GET['id_inbox'])){
            $id_inbox = $_GET['id_inbox'];
            $inbox = $this->model->get_child('inbox');
            if($inbox->exists($id_inbox)){
                $inbox->get($id_inbox);
                $inbox->archivado = "0";
                $inbox->save();
            }
        }

       HttpHandler::redirect('/nymsa/modulo/inboxRead?inboxId=0&Compose=true');
    }

    public function alerta(){

        $this->view->alerta();
    }

}

?>