<?php

/**
 * 
 * Esta clase provee una interfaz para unificar todas las peticiones de carga
 * y actualizacion de datos para los grid presentes en la aplicacion y reducir la 
 * carga de otros ficheros
 * 
 */
import('mdl.view.grid_tables');
import('mdl.model.grid_tables');

class grid_tablesController extends controller {

    public function test_resource() {
        echo "This resource works!";
    }
    
    public function actualizar() {
        $tblname = $_POST['tblname'];
        _updatedata($this, $tblname);
    }

    /* grid para resumen de bodegas omitiendo espacios en blanco */

    public function bodega_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from bodega WHERE nombre is not null AND nombre !=''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from bodega INNER JOIN empleado on encargado = empleado.id_datos WHERE nombre is not null AND nombre !='' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function solicitud_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $user = Session::singleton()->getUser();
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from  orden_compra join proveedor on orden_compra.proveedor = proveedor.id where activa = 1 and aprobado = 0 and denegado = 0 and creadopor='{$user}'";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select *, proveedor.nombre as proveedor_nombre, orden_compra.id as id from orden_compra join proveedor on orden_compra.proveedor = proveedor.id where activa = 1 and aprobado = 0 and denegado = 0 and creadopor='{$user}' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function detalle_solicitud_grid_1($id_orden) {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from  detalle_orden_compra where id_orden = $id_orden";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select detalle_orden_compra.id as id, detalle_orden_compra.id_orden as id_orden, linea.nombre as linea,detalle_orden_compra.estilo as estilo, color.nombre as color, detalle_orden_compra.talla as talla, detalle_orden_compra.cantidad  as cantidad, detalle_orden_compra.costo as costo, producto.codigo_origen as codigo_origen from detalle_orden_compra left join producto on  producto.estilo = detalle_orden_compra.estilo and producto.linea = detalle_orden_compra.linea INNER JOIN linea ON detalle_orden_compra.linea = linea.id INNER JOIN color ON detalle_orden_compra.color = color.id where id_orden=$id_orden limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    /* grid para resumen de colores omitiendo espacios en blanco */

    public function color_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from color WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from color WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function oferta_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from oferta";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from oferta order by fin desc limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function grid_kit_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from producto where linea = 0";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from producto inner join estado_bodega on producto.linea = estado_bodega.linea and producto.estilo = estado_bodega.estilo inner join control_precio on control_estilo = producto.estilo and control_precio.linea = producto.linea where producto.linea = 0 order by fecha_ingreso desc limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function grid_kit_elementos_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
         $kit = $_POST['kit'];
        $sql = "select count(*) as cnt from elemento_kit WHERE kit = '{$kit}'";
       
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from elemento_kit WHERE kit = '{$kit}' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function productos_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt  from estado_bodega inner join producto on producto.estilo = estado_bodega.estilo and producto.linea = estado_bodega.linea inner join color on color.id = estado_bodega.color inner join control_precio on estado_bodega.estilo = control_estilo and estado_bodega.linea = control_precio.linea where bodega = 1 and estado_bodega.linea != 0 and stock > 0 and precio > 0 group by estado_bodega.estilo, estado_bodega.linea order by fecha_ingreso desc";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select descripcion, estado_bodega.linea, estado_bodega.estilo, estado_bodega.color, estado_bodega.talla, color.nombre as color_nombre, SUM(stock) as stock, precio  from estado_bodega inner join producto on producto.estilo = estado_bodega.estilo and producto.linea = estado_bodega.linea inner join color on color.id = estado_bodega.color inner join control_precio on estado_bodega.estilo = control_estilo and estado_bodega.linea = control_precio.linea where bodega = 1 and estado_bodega.linea != 0 and stock > 0 and precio > 0 group by estado_bodega.estilo, estado_bodega.linea order by fecha_ingreso desc limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    /* grid para resumen de lineas omitiendo espacios en blanco */

    public function linea_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from linea WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from linea WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function grupo_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from grupo WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from grupo WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function concepto_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from concepto WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from concepto WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function grid_retaceo_pendiente_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from hoja_retaceo where aplicada = 0";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from hoja_retaceo where aplicada = 0 limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function grid_retaceo_aplicado_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from hoja_retaceo where aplicada = 1";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from hoja_retaceo where aplicada = 1 limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function suela_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from suela WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from suela WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function tacon_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from tacon WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from tacon WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function ofertados_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from estado_bodega where bodega = 3";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from estado_bodega where bodega = 3 limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function genero_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from genero WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from genero WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function material_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from material WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from material WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function proveedor_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from proveedor WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from proveedor WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function catalogo_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from catalogo WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from catalogo WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function marca_grid_1() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from marca WHERE nombre is not null AND nombre != ''";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from marca WHERE nombre is not null AND nombre != '' limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }

    public function promocion_detalle($id_promocion) {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from promocion_producto WHERE id_promocion=$id_promocion";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
            $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
            $sql = "select * from promocion_producto WHERE id_promocion=$id_promocion limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
            $handle = mysqli_query(conManager::getConnection(), $sql);
            $retArray = array();
            while ($row = mysqli_fetch_object($handle)):
                $retArray[] = array_map('utf8_encode', (array) $row);
            endwhile;
            $data = json_encode($retArray);
            $ret = "{data:" . $data . ",\n";
            $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
            $ret .= "recordType : 'object'}";
            echo $ret;
        endif;
    }
    
    public function dsvdiah() {
        header('Content-type:text/javascript;charset=UTF-8');
        $json = json_decode(stripslashes($_POST["_gt_json"]));
        $pageNo = $json->{'pageInfo'}->{'pageNum'};
        $pageSize = 10; //10 rows per page
        //to get how many records totally.
        $sql = "select count(*) as cnt from cambio WHERE activo = 1";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $row = mysqli_fetch_object($handle);
        $totalRec = $row->cnt;
        //make sure pageNo is inbound
        if ($pageNo < 1 || $pageNo > ceil(($totalRec / $pageSize))):
        $pageNo = 1;
        endif;
        if ($json->{'action'} == 'load'):
        $sql = "select * from cambio WHERE activo = 1 ORDER BY id DESC limit " . ($pageNo - 1) * $pageSize . ", " . $pageSize . ";";
        $handle = mysqli_query(conManager::getConnection(), $sql);
        $retArray = array();
        while ($row = mysqli_fetch_object($handle)):
            $retArray[] = array_map('utf8_encode', (array) $row);
        endwhile;
        $data = json_encode($retArray);
        $ret = "{data:" . $data . ",\n";
        $ret .= "pageInfo:{totalRowNum:" . $totalRec . "},\n";
        $ret .= "recordType : 'object'}";
        echo $ret;
        endif;
    }

}

?>