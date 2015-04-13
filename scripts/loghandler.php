<?php

function write_new_log($cadena) {
    $fecha = "Fecha:" . date("y-m-d");
    $hora = date("h:m:s");
    $fecha .= " ($hora)\t\t";
    $cadena = $fecha . $cadena;
    if (!is_writable(APP_PATH . 'scripts/' . LOG)) {
        echo "No es escribible " . LOG;
        exit;
    } else {
        $archivo = fopen(APP_PATH . 'scripts/' . LOG, 'a');
        if (!(fwrite($archivo, $cadena . PHP_EOL))) {
            echo "no se puede escribir " . LOG;
            exit;
        }
        fclose($archivo);
    }
}

function match($s_str) {
    $archivo = fopen(APP_PATH . 'scripts/' . LOG, 'r') or die('Problemas de permisos');
    $texto = '<table class="log">
					<thead>
						<tr>
							<th>
								Fecha (Hora)
							</th>
							<th>
								Acci&oacute;n
							</th>
							<th>
								Usuario
							</th>
						</tr>
					</thead>';
    while (!feof($archivo)) {
        $tildes = array('á', 'é', 'í', 'ó', 'ú');
        $reemplazo = array('&aacute;', '&eacute;', '&iacute;', '&oacute;', '&uacute;');
        $cadena = str_replace($tildes, $reemplazo, fgets($archivo));
        $fecha = strpos($cadena, 'Fecha:');
        $modulo = strpos($cadena, 'Modulo:');
        $accion = strpos($cadena, 'Accion:');
        $usuario = strpos($cadena, 'Usuario:');
        $len = strlen($cadena);
        $fecha_str = substr($cadena, $fecha + 6, ($modulo - $fecha - 6)) . "<br/>";
        $modulo_str = substr($cadena, $modulo + 7, ($accion - $modulo - 7)) . "<br/>";
        $accion_str = substr($cadena, $accion + 7, ($usuario - $accion - 7)) . "<br/>";
        $usuario_str = substr($cadena, $usuario + 8, ($len - $accion - 8)) . "<br/>";
        if (strpos($modulo_str, $s_str) !== false) {
            $texto.="<tr><td>$fecha_str</td><td>$accion_str</td><td>$usuario_str</td></tr>";
        }
    }
    $texto.="</table>";
    fclose($archivo);
    return $texto;
}

function clean($s_str) {
    $archivo = fopen(APP_PATH . 'scripts/' . LOG, 'r') or die('Problemas de permisos');
    $texto = "";
    while (!feof($archivo)) {
        $cadena = fgets($archivo);
        $modulo = strpos($cadena, 'Modulo:');
        $accion = strpos($cadena, 'Accion:');
        $modulo_str = substr($cadena, $modulo + 7, ($accion - $modulo - 7)) . "<br/>";
        if (strpos($modulo_str, $s_str) !== false) {
            #No hacemos nada
        } else {
            $texto.=$cadena . EOL;
        }
    }
    fclose($archivo);
    $archivo = fopen(APP_PATH . 'scripts/' . LOG, 'w');
    fwrite($fp, $texto);
    fclose($archivo);
}

?>