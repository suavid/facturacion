<?php

session_start();

error_reporting(E_ALL);

require_once('settings/settings.php');
require_once('scripts/encryption.php');

require_once('../SystemCore/bm_engine.php');

date_default_timezone_set(TIMEZONE);

require_once('../SystemCore/engine/template.php');
require_once('../SystemCore/engine/page.php');
require_once('../SystemCore/engine/frontController.php');
require_once('../SystemCore/handler/http.php');
require_once('../SystemCore/handler/controller.php');
require_once('../SystemCore/handler/sessionHandler.php');

import('scripts.alias');
import('scripts.init_setup');

require_once "../UI-Core/plugins/sigma/demos/export_php/GridServerHandler.php";
require_once "../UI-Core/plugins/sigma/php/ConnectionManager.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/JSON.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/convert.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/_class/exception.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/_class/locale.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/_class/myPdf.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/_class/parsingHtml.php";
require_once "../UI-Core/plugins/sigma/demos/export_php/html2pdf/_class/parsingCss.php";

import('mdl.error');

#####	configuraciones iniciales	#####

$plugins = array(
    "metro-global",
    "metro-core",
    "metro-locale",
    "metro-initiator",
    "metro-accordion",
    "metro-button-set",
    "metro-carousel",
    "metro-countdown",
    "metro-date-format",
    "metro-dialog",
    "metro-drag-tile",
    "metro-dropdown",
    "metro-fluentmenu",
    "metro-hint",
    "metro-input-control",
    "metro-listview",
    "metro-live-tile",
    "metro-loader",
    "metro-notify",
    "metro-panel",
    "metro-plugin-template",
    "metro-popover",
    "metro-progressbar",
    "metro-pull",
    "metro-rating",
    "metro-scroll",
    "metro-slider",
    "metro-stepper",
    "metro-streamer",
    "metro-tab-control",
    "metro-table",
    "metro-tile-transform",
    "metro-times",
    "metro-touch-handler",
    "metro-treeview",
    "metro-wizard",
    "metro-calendar",
    "metro-datepicker"
);

BM::singleton()->storeObject('template', 'temp');
BM::singleton()->storeSetting('default', 'skin');

BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/jquery.min.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/jquery-ui.min.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/angular.min.js');

BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/app.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/constants.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/notificationService.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/facturacion.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/caja.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/pedido.js');

BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/plugins/sigma/grid/gt_grid.css');
BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/plugins/sigma/grid/skin/mac/skinstyle.css');
BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/plugins/sigma/grid/skin/vista/skinstyle.css');
BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/css/metro-bootstrap-responsive.css');
BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/css/metro-bootstrap.css');
BM::singleton()->getObject('temp')->getPage()->setCss('../UI-Core/css/iconFont.min.css');

BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/core.js');

foreach ($plugins as $plugin) {
    BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/metro/' . $plugin . '.js');
}

BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/plugins/sigma/grid/calendar/calendar.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/plugins/sigma/grid/calendar/calendar-setup.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/plugins/sigma/grid/gt_grid_all.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/plugins/sigma/grid/gt_msg_en.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/plugins/sigma/grid/flashchart/fusioncharts/FusionCharts.js');
BM::singleton()->getObject('temp')->getPage()->setJs('../UI-Core/js/hot.js');

$front = new frontController(array());
$front->run();

exit();

?>