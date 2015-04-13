<?php

function verificar_permiso_modulo($modulo) {
    $nivel = ($_SESSION['level'] == 0) ? true : false;
    $usuario = $_SESSION['user'];
    $permiso_modulo = false;
    $permiso_usuario = false;
    $acceso_por_grupo_query = "select permiso from permiso_modulo_grupo 
								   INNER JOIN grupo_usuario ON 
								   permiso_modulo_grupo.id_grupo = grupo_usuario.id_grupo 
								   WHERE id_usuario = '$usuario' AND modulo='$modulo';";

    $acceso_usuario_query = "select * from permiso_modulo_usuario 
								 WHERE id_usuario='$usuario' AND modulo='$modulo'";

    BM::singleton()->getObject('db')->executeQuery($acceso_por_grupo_query);
    if (BM::singleton()->getObject('db')->getNumRows() > 0) {
        while ($data = BM::singleton()->getObject('db')->getResult()->fetch_assoc()) {
            $permiso_modulo = ($data['permiso'] == 0) ? true : false;
        }
    }

    BM::singleton()->getObject('db')->executeQuery($acceso_usuario_query);
    if (BM::singleton()->getObject('db')->getNumRows() > 0) {
        while ($data = BM::singleton()->getObject('db')->getResult()->fetch_assoc()) {
            $permiso_usuario = ($data['permiso'] == 0) ? true : false;
        }
    }

    if ($nivel || $permiso_modulo || $permiso_usuario)
        return true;
    else {
        return false;
    }
}

function validar_sesion_proveedor() {
    if (isset($_SESSION['proveedor']) && $_SESSION['proveedor'])
        return true;
    else {
        return false;
    }
}

function acceso_proveedor() {
    if (!validar_sesion_proveedor()) {
        HttpHandler::redirect('/nymsa_testing/mdl/Proveedor/ver');
    }
}

function proveedor_activo() {
    if (validar_sesion_proveedor()) {
        HttpHandler::redirect('/nymsa_testing/mdl/Proveedor/acceder');
    }
}

?>