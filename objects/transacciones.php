<?php

class transaccionesModel extends object {

    public function transaccionesTraslados(){
    	$query = "SELECT * FROM transacciones WHERE GRUPO = 'IN' AND cod!='1A' AND cod!='1D' AND cod!='2D' AND cod!='CA' ";
    	return data_model()->cacheQuery($query);
    }
}

?>