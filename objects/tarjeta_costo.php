<?php

class tarjeta_costoModel extends object {
    public function get_id($documento, $estilo, $color){
    	$query = "SELECT id FROM tarjeta_costo WHERE CESTILO=$estilo AND CCOLOR=$color AND NODOC=$documento";
    	data_model()->executeQuery($query);
    	if(data_model()->getNumRows() > 0){
	    	$res = data_model()->getResult()->fetch_assoc();
	    	return $res['id'];
    	}else{
    		return 0;
    	}
    }
}

?>