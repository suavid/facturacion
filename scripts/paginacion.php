<?php

function paginar($numeroRegistros, $url) {
    $tamPag = 10; # cantidad de registros por pagina 
    if (!isset($_GET["pag"]) || empty($_GET["pag"])) {
        $pagina = 1; # pagina por default 
        $inicio = 1; # pagina de inicio
        $final = $tamPag; # ultima pagina
    } else {
        $pagina = (is_numeric($_GET["pag"]) && (intval($_GET["pag"]) == $_GET["pag"] )) ? $_GET["pag"] : 1; # obtiene pagina de la url
    }

    $limitInf = ($pagina - 1) * $tamPag;
    $numPags = ceil($numeroRegistros / $tamPag);
    if (!isset($pagina)) {
        $pagina = 1;
        $inicio = 1;
        $final = $tamPag;
    } else {
        $seccionActual = intval(($pagina - 1) / $tamPag);
        $inicio = ($seccionActual * $tamPag) + 1;

        if ($pagina < $numPags) {
            $final = $inicio + $tamPag - 1;
        } else {
            $final = $numPags;
        }

        if ($final > $numPags) {
            $final = $numPags;
        }
    }

    $paginacion_str = '<span class="paginacion">';

    /* genera una cadea con html para establecer los enlaces de la paginacion */
    if ($pagina > 1) {
        $paginacion_str.= "<a href='{$url}pag=" . ($pagina - 1) . "'>";
        $paginacion_str.= "anterior";
        $paginacion_str.= "</a> ";
    }

    for ($i = $inicio; $i <= $final; $i++) {
        if ($i == $pagina) {
            $paginacion_str.= "<b>" . $i . "</b>";
        } else {
            $paginacion_str.= "<a href='{$url}pag=" . $i . "'>";
            $paginacion_str.= "&nbsp;" . $i . "</a> ";
        }
    }
    if ($pagina < $numPags) {
        $paginacion_str.= " <a href='{$url}pag=" . ($pagina + 1) . "'>";
        $paginacion_str.= "siguiente</a>";
    }

    $paginacion_str.="</span>";

    # devuelve la cedena, el limite inferior y el tamaño de pagina para la consulta sql
    return array($paginacion_str, $limitInf, $tamPag);
}

?>
