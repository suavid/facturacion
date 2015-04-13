<?php

class documentoModel extends object {
    public function documentos($limitInf, $tamPag){
    	$query = "SELECT * FROM documento ORDER BY id_documento DESC LIMIT $limitInf, $tamPag";
    	return data_model()->cacheQuery($query);
    }
}

?>