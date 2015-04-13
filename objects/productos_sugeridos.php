<?php

class productos_sugeridosModel extends object {

    public function obtener_productos_sugeridos($linea, $estilo, $type =  "cache"){
    	$query = "SELECT estilo_sugerencia FROM productos_sugeridos WHERE linea = $linea AND estilo = '{$estilo}'";

    	if($type=="cache"){
    		
    		return data_model()->cacheQuery($query);
    	
    	}else if($type=="data"){
    		
    		data_model()->executeQuery($query);
    		
    		$resp = array();

    		while ($res = data_model()->getResult()->fetch_assoc()) {
    			$resp[] = $res;
    		}

    		return $resp;
    	}

    	return null;
    }
}

?>