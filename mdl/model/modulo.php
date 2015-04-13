<?php

class moduloModel extends object {

    public function __construct() {
        
    }

    public function actualizar_campo($campo, $valor) {
        $query = "UPDATE campo_actualizacion set actualizar=$valor WHERE nombre_campo='{$campo}'";
        data_model()->executeQuery($query);
    }

    public function habilitar() {
        $query = "UPDATE cliente SET requiere_actualizar = true;";
        data_model()->executeQuery($query);
    }

    public function cargar_campos() {
        $query = "SELECT * FROM campo_actualizacion";
        data_model()->executeQuery($query);
        $retArray = array();
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $retArray[] = $res;
        }
        echo json_encode($retArray);
    }

    public function obtener_actualizables() {
        $query = "SELECT * FROM campo_actualizacion WHERE actualizar=1";
        data_model()->executeQuery($query);
        $retArray = array();
        while ($res = data_model()->getResult()->fetch_assoc()) {
            $retArray[] = $res;
        }
        return $retArray;
    }

    public function inboxPendientes(){
        $usuario = Session::singleton()->getUser();
        $query   = "SELECT COUNT(*) AS noleidos FROM inbox WHERE destinatario='{$usuario}' AND leido = 0";
        data_model()->executeQuery($query);
        $resp    = data_model()->getResult()->fetch_assoc();
        echo json_encode($resp);
    }

    public function inboxPreview(){
        $usuario = Session::singleton()->getUser();
        $query   = "SELECT * FROM inbox WHERE destinatario='{$usuario}' AND archivado = 0 ORDER BY id DESC LIMIT 5 ";
        data_model()->executeQuery($query);
        $resp = array();
        while($rest    = data_model()->getResult()->fetch_assoc()){
            $resp[] = $rest;
        }
        echo json_encode($resp);   
    }

    public function leerBuzon($leidos, $cantidad){
        $usuario = Session::singleton()->getUser();
        $query   = "SELECT * FROM inbox WHERE destinatario='{$usuario}' AND archivado = 0 ORDER BY id DESC LIMIT $leidos, $cantidad";
        data_model()->executeQuery($query);
        $resp = array();
        while($rest    = data_model()->getResult()->fetch_assoc()){
            $resp[] = $rest;
        }
        echo json_encode($resp);   
    }

    public function leerBuzonArchivados($leidos, $cantidad){
        $usuario = Session::singleton()->getUser();
        $query   = "SELECT * FROM inbox WHERE destinatario='{$usuario}' AND archivado = 1 ORDER BY id DESC LIMIT $leidos, $cantidad";
        data_model()->executeQuery($query);
        $resp = array();
        while($rest    = data_model()->getResult()->fetch_assoc()){
            $resp[] = $rest;
        }
        echo json_encode($resp);   
    }

    public function leerBuzonSalida($leidos, $cantidad){
        $usuario = Session::singleton()->getUser();
        $query   = "SELECT * FROM inbox WHERE remitente='{$usuario}' ORDER BY id DESC LIMIT $leidos, $cantidad";
        data_model()->executeQuery($query);
        $resp = array();
        while($rest    = data_model()->getResult()->fetch_assoc()){
            $resp[] = $rest;
        }
        echo json_encode($resp);   
    }

}

?>