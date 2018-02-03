(function () {
    'use strict';

    angular
        .module('ERPapp')
        .controller('PedidoController', pedido);

    pedido.$inject = ['$location', '$http', 'notificationService']; 

    function pedido($location, $http, notificationService) {
        var vm = this;
       
        // init setup
        inicializarVariables();
        ObtenerDatosCaja();

        vm.ListaBodegas = [];
        vm.ListaLineas = [];

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
            vm.ListaBodegas = response.data;
        });

        $http.post('/facturacion/factura/ListaLineas', {}, {
            headers: {
                "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
            },
            transformRequest: [function (data) {
                return angular.isObject(data) ?
                    jQuery.param(data) :
                    data;
            }]
        }).then(function (response) {
            vm.ListaLineas = response.data;
            if (vm.ListaLineas.length > 0) {
                vm.bLinea = String(response.data[0].id_categoria_especifica);
            }
        });

        vm.GenerarPedido = function () {
            $http.post('/facturacion/factura/ValidarCliente', { idCliente: vm.codigo_cliente }, {
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
                    if (response.data.length > 0) {
                        vm.ftipo = "ND";
                        vm.festado = "PENDIENTE";
                        establecerDatosDelCliente(response.data[0]);
                        establecerInformacionCrediticia(response.data[0]);

                        if (vm.nPedido == 0)
                            NuevoPedido();

                    } else {
                        notificationService.notifyError("El código de cliente no es válido");
                    }
                }
            });
        };

        vm.CargarPedido = function() {
            $http.post('/facturacion/factura/CargarPedidoExistente', { id_pedido: vm.nPedido }, {
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
                },
                transformRequest: [function (data) {
                    return angular.isObject(data) ?
                        jQuery.param(data) :
                        data;
                }]
            }).then(function (response) {
                if (response.data.length > 0) {
                    vm.nPedido = response.data[0].id_factura;
                    vm.codigo_cliente = response.data[0].id_cliente;
                    angular.element('#idCliente').triggerHandler('blur');
                    vm.PedidoActivo = true;
                    notificationService.notifySuccess("Pedido cargado con éxito!");
                } else {
                    notificationService.notifyError("Número de pedido no válido");
                    vn.nPedido = 0;
                }
            });
        }

        vm.ConsultaExistencias = function () {
            vm.MostrarSelector = true;
            var filtros = [];

            if (StrTrim(vm.bEstilo) != "")
                filtros.push(['estilo', vm.bEstilo].join(":"));
            if (StrTrim(vm.bLinea) != "")
                filtros.push(['linea', vm.bLinea].join(":"));
            if (StrTrim(vm.bColor) != "")
                filtros.push(['color', vm.bColor].join(":"));
            if (StrTrim(vm.bTalla) != "")
                filtros.push(['talla', vm.bTalla].join(":"));

            if (StrTrim(vm.Caja.bodega_por_defecto) != "")
                filtros.push(['bodega', vm.Caja.bodega_por_defecto].join(":"));
            
            var grid = Sigma.$grid("stock_grid");
            grid.loadURL = '/facturacion/factura/CargarStock?filtros=' + filtros;
            grid.reload();
        }


        function establecerInformacionCrediticia(data) {
            //montos
            vm.credito = data.credito;
            vm.credito_usado = data.credito_usado;
            vm.disponible = vm.credito - vm.credito_usado;
            vm.monto_extra = data.monto_extra;
            // banderas 
            vm.bloqueado = (data.bloqueado == 0) ? false : true;
            vm.tcredito = (data.tcredito == 0) ? false : true;
            vm.extra_credito = (data.extra_credito == 0) ? false : true;
            vm.transitorio = (data.transitorio == 0) ? false : true;
            vm.empleado = (data.empleado == 0) ? false : true;
        }

        function establecerDatosDelCliente(data) {
            // información general
            vm.nombre_cliente = (data.primer_nombre + ' ' + data.segundo_nombre + ' ' + data.primer_apellido + ' ' + data.segundo_apellido).toUpperCase().replace(/NULL/g, "");
            vm.direccion = data.direccion.toUpperCase();
            vm.telefono_cliente = data.telefono_casa ? data.telefono_casa : data.telefono_celular;
            vm.dias_credito = data.dias_credito;
            // información de contacto
            vm.telefono_casa = data.telefono_casa;
            vm.telefono_oficina = data.telefono_oficina;
            vm.telefono_celular = data.telefono_celular;
        }

        function inicializarVariables() {
            vm.nPedido = 0;
            vm.codigo_cliente = 0;
            vm.PedidoActivo = false;

            vm.nombre_cliente = "";
            vm.direccion = "";
            vm.telefono_cliente = "";
            vm.dias_credito = 0;
            vm.concepto = "VENTA";

            vm.telefono_casa = "";
            vm.telefono_oficina = "";
            vm.telefono_celular = "";

            vm.bloqueado = false;
            vm.tcredito = false;
            vm.extra_credito = false;
            vm.transitorio = false;
            vm.empleado = false;

            vm.credito = 0;
            vm.credito_usado = 0;
            vm.disponible = 0;
            vm.monto_extra = 0;
            vm.Caja = {};

            vm.SubTotal = 0;
            vm.Total = 0;
            vm.Descuento = 0;
            vm.Iva = 0;

            vm.Flete = false;
            vm.CF = false;

            vm.MostrarSelector = false;

            vm.bLinea = null;
            vm.bEstilo = null;
            vm.bColor = null;
            vm.bTalla = null;
        }

        function ObtenerDatosCaja() {
            $http.post('/facturacion/factura/ObtenerDatosDeCaja', { }, {
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
                },
                transformRequest: [function (data) {
                    return angular.isObject(data) ?
                        jQuery.param(data) :
                        data;
                }]
            }).then(function (response) {
                if (response.data.length > 0) {
                    vm.Caja = response.data[0];
                    vm.Caja.bodega_por_defecto = String(vm.Caja.bodega_por_defecto);
                }
            });
        }

        function NuevoPedido() {
            $http.post('/facturacion/factura/GenerarNuevoPedido', { id_cliente: vm.codigo_cliente, concepto: vm.concepto, id_caja: vm.Caja.id}, {
                headers: {
                    "Content-Type": 'application/x-www-form-urlencoded;charset=utf-8'
                },
                transformRequest: [function (data) {
                    return angular.isObject(data) ?
                        jQuery.param(data) :
                        data;
                }]
            }).then(function (response) {
                if (response.data.id_pedido) {
                    if (response.data.id_pedido > 0) {
                        notificationService.notifySuccess("Pedido generado con éxito!");
                        vm.nPedido = response.data.id_pedido;
                        vm.PedidoActivo = true;
                    } else {
                        notificationService.notifyError(response.data.ErrorMessage);
                    }
                } else {
                    if (response.data.error) {
                        notificationService.notifyError(response.data.message);
                    } else {
                        notificationService.notifyError("Ha ocurrido un error inesperado");
                    }
                }
            });
        }

        function StrTrim(x) {
            return x.replace(/^\s+|\s+$/gm, '');
        }

    };

})();
