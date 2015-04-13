<?php

$MODULES = array(
    'inventario' => array('acces' => false, 'subCategory' => array('colores' => false, 'tallas' => false)),
    'factura' => array('acces' => false, 'subCategory' => array()),
    'cliente' => array('acces' => false, 'subCategory' => array('ficha' => false, 'listado' => false))
);

function updateModules($id = '') {
    if ($id == '')
        $str = F_updateModules(Session::singleton()->getUser());
    else
        $str = F_updateModules($id, true);
    if ($str != "") {
        $MODULES = unserialize(base64_decode($str));
    }
}

function unlockAcces($modulo, $cliente) {
    updateModules($cliente);
    $MODULES[$modulo]['acces'] = true;
    $str = base64_encode($this->serializeModules());
    F_unlockAcces($cliente, $str);
    echo json_encode(array('status' => 'OK'));
}

function CheckIfModuleEnabledStr($moduleName) {
    if (isset($_POST['id'])) {
        updateModules($_POST['id']);
    } else {
        updateModules();
    }

    $ret['Module'] = $moduleName;

    if (isset($this->MODULES[$moduleName])) {
        $ret['Result'] = $this->MODULES[$moduleName]['acces'];
    } else {
        $ret['Result'] = false;
    }


    echo json_encode($ret);
}

function serializeModules() {
    return serialize($this->MODULES);
}

?>