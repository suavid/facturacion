(function () {
    'use strict';

    angular
        .module('Facturacion')
        .controller('CajasController', caja);

    caja.$inject = ['$location', '$http'];

    function caja($location, $http) {

        var vm = this;

        vm.listaDeSeries = [];
        vm.listaDeEmpleados = [];
        vm.listaDeBodegas = [];

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

        vm.InsertarCaja = function () {
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
                    alert(response.data.message);
                } else {
                    location.href = location.href;
                }
            });
        };

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
                vm.Caja.encargado = String(vm.listaDeEmpleados[0].usuario);
            }
         });

        function obtenerIdSerie(tipo) {
            for (var i = 0; i < vm.listaDeSeries.length; i++) {
                if (vm.listaDeSeries[i].tipo.trim() == tipo.trim()) {
                    return vm.listaDeSeries[i].id;
                }
            }
            return null;
        }
    }
})();
