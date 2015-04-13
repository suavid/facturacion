<?php

class colorModel extends object {

    public function ultimo_en_blanco() {
        $query = "select id from color WHERE nombre is null or nombre ='' ";
        data_model()->executeQuery($query);
        if (data_model()->getNumRows() > 0) {
            $ret = data_model()->getResult()->fetch_assoc();
            return $ret['id'];
        } else {
            return -1;
        }
    }

}

?>