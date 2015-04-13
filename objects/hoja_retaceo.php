<?php

class hoja_retaceoModel extends object {
    
    public function aplicables(){
        $query = "SELECT * FROM hoja_retaceo WHERE confirmada = 1 AND aplicada = 0";
        return data_model()->cacheQuery($query);
    }
}

?>