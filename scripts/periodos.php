<?php

function cargar_periodos() {
    $query = "SELECT periodo_fiscal, periodo_actual FROM system WHERE id=1";
    data_model()->executeQuery($query);
    $data = data_model()->getResult()->fetch_assoc();
    return array($data['periodo_fiscal'], $data['periodo_actual']);
}

?>