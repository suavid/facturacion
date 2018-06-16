(function () {
    'use strict';

var AplicacionDeFacturacion = angular.module('ERPapp');

    AplicacionDeFacturacion.controller('PrincipalController', PrincipalController);
    AplicacionDeFacturacion.controller('SeriesController', SeriesController);

    AplicacionDeFacturacion.directive('multiSelectChecker', function ($compile) {
    return {
        restrict: 'A',
        replace: false,
        terminal: true, 
        priority: 50000, 
        compile: function compile(element, attrs) {
            element.removeAttr("multi-select-checker");
            element.removeAttr("data-multi-select-checker");

            return {
                pre: function preLink(scope, iElement, iAttrs, controller) { },
                post: function postLink(scope, iElement, iAttrs, controller) {
                    if (scope.categoria.multilinea) {
                        iElement[0].setAttribute('multiple', '');
                    }
                    $compile(iElement)(scope);
                }
            };
        }
    };
});

    function PrincipalController($http, $sce) {
        var vm = this;
        vm.Banners = [];
        vm.Organizacion = {};

        $http.post('/facturacion/factura/ObtenerBanner', { modulo: "VENTAS" }, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.Banners = response.data;

            for (var i = 0; i < vm.Banners.length; i++) {
                vm.Banners[i].descripcion = $sce.trustAsHtml(vm.Banners[i].descripcion);
            }
        });

        $http.post('/facturacion/factura/ObtenerInformacionDelSistema', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.Organizacion = response.data[0];
        });
    }

    function SeriesController($http, $sce) {
    var vm = this;

    vm.Serie = {
        fechaResolucion: '',
        autorizadoDel: 0,
        autorizadoAl: 999999,
        serie: '',
        ultimoUtilizado: 0,
        descripcion: '',
        numeroResolucion: '',
        tipo:0
    };

    vm.listaTipoSeries = [];

    vm.CategoriasDeSeries = function () {
        $http.post('/facturacion/factura/CategoriasDeSeries', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.listaTipoSeries = response.data;
            if (vm.listaTipoSeries.length > 0) {
                vm.Serie.tipo = vm.listaTipoSeries[0].id; 
            }
        });
    };

    vm.InsertarSerie = function () {

        vm.Serie.fechaResolucion = angular.element('#datePicker').val();

        $http.post('/facturacion/factura/InsertarSerie', vm.Serie, {
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

    vm.CategoriasDeSeries();
}

})();