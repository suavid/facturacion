<?php

class municipioModel extends object {
    public function cargar_municipios($departamento){
    	$query = "SELECT * FROM municipio WHERE id_departamento = $departamento";
    	data_model()->executeQuery($query);
    	$res = array();
    	$res['municipios'] = array();
    	while($ret = data_model()->getResult()->fetch_assoc()){
    		//echo var_dump($res);
    		$res['municipios'][] = $ret;
    		$res['municipios'][count($res['municipios'])-1]['nombre'] = utf8_encode($res['municipios'][count($res['municipios'])-1]['nombre']);
    	}

    	echo json_encode($res);
    }
}

?>