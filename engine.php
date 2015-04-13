<?php

session_start();

error_reporting(E_ALL);

define("BUSINESS_MANAGER", false); # aplicacion en produccion

require_once('settings/settings.php');  # carga configuraciones
require_once('scripts/encryption.php'); # funciones de proteccion de datos

import('core.bm_engine');

date_default_timezone_set(TIMEZONE);

import('core.engine.database');
import('core.engine.template');
import('core.engine.page');
import('core.engine.frontController');
import('core.handler.http');
import('core.handler.controller');
import('core.handler.object');
import('core.handler.sessionHandler');
import('core.handler.MysqliHandler');
import('core.handler.debugHandler');
import('core.orm.helper');

import('scripts.acceso');
import('scripts.loghandler');
import('scripts.pdf.fpdf');
import('scripts.pdf.report');
import('scripts.alias');
import('scripts.init_setup');
import('common.plugins.sigma.demos.export_php.GridServerHandler');
import('common.plugins.sigma.php.ConnectionManager');
import('common.plugins.sigma.demos.export_php.JSON');
import('common.plugins.sigma.demos.export_php.html2pdf.convert');
import('common.plugins.sigma.demos.export_php.html2pdf._class.exception');
import('common.plugins.sigma.demos.export_php.html2pdf._class.locale');
import('common.plugins.sigma.demos.export_php.html2pdf._class.myPdf');
import('common.plugins.sigma.demos.export_php.html2pdf._class.parsingHtml');
import('common.plugins.sigma.demos.export_php.html2pdf._class.parsingCss');

import('mdl.error');

#####	configuraciones iniciales	#####

//$conManager = new ConManager();
//$pgConexion = $conManager->getConnection();

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
BM::singleton()->storeObject('database', 'db');
BM::singleton()->storeSetting('default', 'skin');

BM::singleton()->getObject('temp')->getPage()->setJs('static/js/jquery.min.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/angular.min.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/jquery.widget.min.js');

BM::singleton()->getObject('temp')->getPage()->setCss('common/plugins/sigma/grid/gt_grid.css');
BM::singleton()->getObject('temp')->getPage()->setCss('common/plugins/sigma/grid/skin/mac/skinstyle.css');
BM::singleton()->getObject('temp')->getPage()->setCss('common/plugins/sigma/grid/skin/vista/skinstyle.css');
BM::singleton()->getObject('temp')->getPage()->setCss('static/css/metro-bootstrap-responsive.css');
BM::singleton()->getObject('temp')->getPage()->setCss('static/css/metro-bootstrap.css');
BM::singleton()->getObject('temp')->getPage()->setCss('static/css/iconFont.min.css');

BM::singleton()->getObject('temp')->getPage()->setJs('static/js/business.manager.1.0.js');

foreach ($plugins as $plugin) {
    BM::singleton()->getObject('temp')->getPage()->setJs('static/js/metro/' . $plugin . '.js');
}

BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/calendar/calendar.js');
BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/calendar/calendar-setup.js');
BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/gt_grid_all.js');
//BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/gt_const.js');
BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/gt_msg_en.js');
BM::singleton()->getObject('temp')->getPage()->setJs('common/plugins/sigma/grid/flashchart/fusioncharts/FusionCharts.js');
BM::singleton()->getObject('temp')->getPage()->setJs('static/js/hot.js');

BM::singleton()->getObject('db')->newConnection(HOST, USER, PASSWORD, DATABASE);

//CANCELAR_OFERTAS();
#####	fin de configuraciones #####

$front = new frontController(array());  # crear controlador 'front'
$front->run();        # correr controlador 'front'
exit();
?>