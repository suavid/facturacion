<?php

class catalogoModel extends object {

    public function TopList() {
        $query = "SELECT * FROM catalogo LIMIT 4";
        return data_model()->cacheQuery($query);
    }

}

?>