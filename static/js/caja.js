(function () {
    'use strict';

    angular
        .module('ERPapp')
        .controller('CajasController', caja);

    caja.$inject = ['$scope','$location', '$http', 'notificationService'];

    function caja($scope, $location, $http, notificationService) {

        var vm = this;

        // List variables
        vm.listaDeSeries = [];
        vm.listaDeEmpleados = [];
        vm.listaDeBodegas = [];

        // New record
        vm.Caja = {};

        // Init objects
        inicializarObjetos();

        // Public function
        vm.InsertarCaja = function () {

            vm.Caja.pCambioBodega = vm.Caja.pCambioBodega ? 1 : 0; 

            $http.post('/facturacion/factura/RegistrarCaja', vm.Caja, {
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
                },
                transformRequest: [function (data) {
                    return angular.isObject(data) ?
                        jQuery.param(data) :
                        data;
                }]
            }).then(function (response) {
                if (response.data.error) {
                    notificationService.notifyError(response.data.message);
                } else {
                    inicializarObjetos();
                    notificationService.notifySuccess("Registro ingresado correctamente");
                    var grid = Sigma.$grid("caja_grid");
                    grid.loadURL = '/facturacion/factura/ObtenerListaDeCajas';
                    grid.reload();
                }
            });
        };

        // Init request
        $http.post('/facturacion/factura/ObtenerSeries', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.listaDeSeries = response.data;

            if (vm.listaDeSeries.length > 0) {
                // Set default values for dropdowns
                vm.Caja.serieFactura = String(obtenerIdSerie('FC'));
                vm.Caja.serieNotaCredito = String(obtenerIdSerie('NC'));
                vm.Caja.serieNotaDebito = String(obtenerIdSerie('ND'));
                vm.Caja.serieRecibo = String(obtenerIdSerie('RC'));
                vm.Caja.serieTicket = String(obtenerIdSerie('TI'));
                vm.Caja.serieCreditofiscal = String(obtenerIdSerie('CF'));
                vm.Caja.serieNotaRemision = String(obtenerIdSerie('NR'));
            }
        });

        $http.post('/facturacion/factura/ListaBodegas', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.listaDeBodegas = response.data;
            if (vm.listaDeBodegas.length > 0) {
                // Set default values for dropdowns
                vm.Caja.bodegaPorDefecto = String(vm.listaDeBodegas[0].id);
            }
        });

        $http.post('/facturacion/factura/ObtenerEmpleados', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.listaDeEmpleados = response.data;
            if (vm.listaDeEmpleados.length > 0) {
                // Set default values for dropdowns
                vm.Caja.encargado = String(vm.listaDeEmpleados[0].usuario);
            }
         });

        // private functions
        function obtenerIdSerie(tipo) {
            for (var i = 0; i < vm.listaDeSeries.length; i++) {
                if (vm.listaDeSeries[i].tipo.trim() == tipo.trim()) {
                    return vm.listaDeSeries[i].id;
                }
            }
            // no record found
            return null;
        }

        function CargarDatosCaja() {
            $http.post('/facturacion/factura/ObtenerCaja', vm.Caja, {
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
                },
                transformRequest: [function (data) {
                    return angular.isObject(data) ?
                        jQuery.param(data) :
                        data;
                }]
            }).then(function (response) {
                vm.Caja.nombre = response.data[0].nombre;
                vm.Caja.pCambioBodega = (response.data[0].p_cambio_bodega == 1) ? true: false;
                vm.Caja.encargado = String(response.data[0].encargado);
                vm.Caja.ultimoPedido = response.data[0].ultimo_pedido;
                vm.Caja.ultimoCambio = response.data[0].ultimo_cambio;
                vm.Caja.bodegaPorDefecto = String(response.data[0].bodega_por_defecto);
                vm.Caja.serieFactura = String(response.data[0].serie_factura);
                vm.Caja.serieNotaCredito = String(response.data[0].serie_nota_credito);
                vm.Caja.serieNotaDebito = String(response.data[0].serie_nota_debito);
                vm.Caja.serieRecibo = String(response.data[0].serie_recibo);
                vm.Caja.serieTicket = String(response.data[0].serie_ticket);
                vm.Caja.serieCreditofiscal = String(response.data[0].serie_credito_fiscal);
                vm.Caja.serieNotaRemision = String(response.data[0].serie_nota_remision);
            });
        };

        function inicializarObjetos() {
            vm.Caja = {
                id: 0,
                nombre: null,
                encargado: null,
                ultimoPedido: 0,
                ultimoCambio: 0,
                bodegaPorDefecto: null,
                serieFactura: null,
                serieNotaCredito: null,
                serieNotaDebito: null,
                serieRecibo: null,
                serieTicket: null,
                serieCreditofiscal: null,
                serieNotaRemision: null,
                pCambioBodega: false
            };
        }

        // watchers
        $scope.$watch("vm.Caja.id", function (newValue, oldValue) {

            if (newValue === oldValue) {
                return;
            }

            if (newValue != 0) 
                CargarDatosCaja();
        });

    }
})();
