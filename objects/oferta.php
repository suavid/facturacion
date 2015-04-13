<?php

class ofertaModel extends object {

    public function consultar_oferta($linea, $estilo, $color, $talla) {
        $ret   = array();
        $query = "SELECT id_oferta FROM oferta_producto INNER JOIN oferta ON id_oferta = oferta.id WHERE linea=$linea AND estilo='{$estilo}' AND color=$color AND talla=$talla AND vencida = 1";
        data_model()->executeQuery($query);

        while ($data = data_model()->getResult()->fetch_assoc()) {
            $ret[] = $data['id_oferta'];
        }

        $response = array();

        foreach ($ret as $id) {
            $query = "SELECT * FROM oferta WHERE id = $id";
            data_model()->executeQuery($query);
            $response[] = data_model()->getResult()->fetch_assoc();
        }

        echo json_encode($response);
    }

}

?>