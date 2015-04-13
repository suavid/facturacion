<?php

$config_file = $_SERVER['DOCUMENT_ROOT'] . "/BM_CONFIG/facturacion/options.conf";

define('CONFIG_FILE', $config_file);

function init_set() {
    $archivo = file(CONFIG_FILE);
    foreach ($archivo as $linea):
        if ($contenido = strpos($linea, ";") != False):
            $line = substr($linea, 0, $contenido);
        endif;
        if (strpos($linea, "=") != False):
            list($variable, $valor) = explode("=", $linea);
            define(trim($variable), trim($valor));
        endif;
    endforeach;
    ini_set('include_path', APP_PATH); # change include path
}

# easy import

function import($module) {
    $module_name = str_replace('.', '/', $module);
    if (!file_exists(APP_PATH . "{$module_name}.php"))
        throw new \InvalidArgumentException("Fatal error: No module named {$module} ");
    require_once "{$module_name}.php";
}

function isInstalled($systemName) {
    $dirParts = explode("\\", APP_PATH);
    $comp     = count($dirParts);
    $pathRes  = "";

    for ($i=0; $i < ($comp-2); $i++) { 
        $pathRes .= $dirParts[$i] . "\\";
    }

    $pathRes .= $systemName;

    $res = is_dir($pathRes);

    return $res;
}

function connectTo($systemName, $systemResource, $modelName){
    $dirParts = explode("\\", APP_PATH);
    $comp     = count($dirParts);
    $pathRes  = "";

    for ($i=0; $i < ($comp-2); $i++) { 
        $pathRes .= $dirParts[$i] . "\\";
    }

    $pathRes .= $systemName ."\\";

    ini_set('include_path', $pathRes);

    $module_name = str_replace('.', '/', $systemResource);
  
    if (!file_exists($pathRes . "{$module_name}.php"))
        throw new \InvalidArgumentException("Fatal error: No module named {$systemResource} ");
    require_once "{$module_name}.php";

    ini_set('include_path', APP_PATH);   

    $cls = "{$modelName}Model";
    $child_obj = new $cls();
    return $child_obj;
}

init_set();

set_error_handler('error_handler');

function error_handler($errno, $errstr, $errfile, $errline) {
    //$fp = fopen(APP_PATH."error.log", "a");
    //fputs($fp, $errno.": ".$errstr." - url: ".$_SERVER['REQUEST_URI']." - line: ".$errline." - file: ".$errfile.PHP_EOL);
    //fclose($fp);

    if (4096 == $errno){
        throw new Exception($errstr);
    }
    
    return false;
}

function throw_error($errorMsg){
    $fp = fopen(APP_PATH."error.log", "a");
    fputs($fp, $errorMsg);
    fclose($fp);    
}

?>
