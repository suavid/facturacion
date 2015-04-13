<?php

class ORMHelper {

    public static function analize($obj) {
        $cls = get_class($obj);
        $name = str_replace('Model', '', $cls);
        $vars = array();
        $id = 0;
        $auto = false;
        data_model()->executeQuery("DESCRIBE $name");
        
        while ($data = data_model()->getResult()->fetch_assoc()):
            $vars[] = $data['Field'];
            if ($data['Key'] === 'PRI')
                $id = $data['Field'];
            if (strpos($data['Extra'], 'auto_increment') !== false)
                $auto = true;
        endwhile;
        return array($name, $vars, $id, $auto);
    }

}

?>