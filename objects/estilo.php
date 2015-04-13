<?php

class estiloModel extends object {
    public function findData($id_prod){
    	$query = "SELECT linea, estilo, color, talla FROM estilo WHERE id_prod=$id_prod";

    	data_model()->executeQuery($query);

    	$res = data_model()->getResult()->fetch_assoc();

    	if(data_model()->getNumRows()>0)
    		return array($res['linea'], $res['estilo'], $res['color'], $res['talla']);
    	else
    		return array(-1,-1, -1, -1);
    }
}

?>