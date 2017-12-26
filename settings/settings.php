<?php

$config_file = $_SERVER['DOCUMENT_ROOT'] . "/facturacion/ModuloFacturacion.ini";

define('CONFIG_FILE', $config_file);

function init_set() 
{
    $archivo = file(CONFIG_FILE);
    foreach ($archivo as $linea):
        if (strpos($linea, "=") != False):
            list($variable, $valor) = explode("=", $linea);
            define(trim($variable), trim($valor));
        endif;
    endforeach;
    ini_set('include_path', APP_PATH); # change include path
}

# easy import

function import($module) 
{
    $module_name = str_replace('.', '/', $module);
    if (!file_exists(APP_PATH . "{$module_name}.php"))
        throw new \InvalidArgumentException("Fatal error: No module named {$module} ");
    require_once "{$module_name}.php";
}

function isInstalled($systemName) 
{
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


init_set();

set_error_handler('error_handler');

function error_handler($errno, $errstr, $errfile, $errline) 
{
    //$fp = fopen(APP_PATH."error.log", "a");
    //fputs($fp, $errno.": ".$errstr." - url: ".$_SERVER['REQUEST_URI']." - line: ".$errline." - file: ".$errfile.PHP_EOL);
    //fclose($fp);
    if (4096 == $errno || 256 == $errno){
        throw new Exception($errstr);
    }

    return false;
}

function throw_error($errorMsg)
{
    $fp = fopen(APP_PATH."error.log", "a");
    fputs($fp, $errorMsg);
    fclose($fp);
}

?>
