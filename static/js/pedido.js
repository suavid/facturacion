(function () {
    'use strict';

    angular
        .module('ERPapp')
        .controller('PedidoController', pedido);

    pedido.$inject = ['$location', '$http', 'notificationService']; 

    function pedido($location, $http, notificationService) {
        var vm = this;

        vm.ReportServerURL = "";
       
        // init setup
        inicializarVariables();
        ObtenerDatosCaja();

        vm.ListaBodegas = [];
        vm.ListaLineas = [];

        vm.idPedido = 0;

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
                    vm.nPedido = response.data[0].fac_serialnumber;
                    vm.idPedido = response.data[0].id_factura;
                    vm.codigo_cliente = response.data[0].client_serialnumber;
                    angular.element('#idCliente').triggerHandler('blur');
                    vm.PedidoActivo = true && response.data[0].editable;
                    actualizarDatosPedido(response.data[0]);
                    notificationService.notifySuccess("Pedido cargado con éxito!");
                } else {
                    notificationService.notifyError("Número de pedido no válido");
                    vn.nPedido = 0;
                }
            });
        }

        vm.ActualizarPedido = function () {
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
                    vm.nPedido = response.data[0].fac_serialnumber;
                    vm.idPedido = response.data[0].id_factura;
                    vm.codigo_cliente = response.data[0].client_serialnumber;
                    angular.element('#idCliente').triggerHandler('blur');
                    vm.PedidoActivo = true && response.data[0].editable;
                    actualizarDatosPedido(response.data[0]);
                } else {
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

        vm.AplicarSeleccion = function () {
            vm.MostrarSelector = false;
            var result = document.getElementsByClassName("items");
            var sendData = {data:[]};

            for (var element in result) {
                var parts = String(result[element].id).split("_");
                var linea = parts[0];
                var estilo = parts[1];
                var color = parts[2];
                var talla = parts[3];
                var cantidad = result[element].value;
                var bodega = vm.Caja.bodega_por_defecto;
                var pedido = vm.idPedido;

                if (cantidad > 0) {
                    sendData.data.push({ "linea": linea, "estilo": estilo, "color": color, "talla": talla, "cantidad": cantidad, "bodega": bodega, "pedido": pedido });
                }
            }

            $http.post('/facturacion/factura/InsertarDetallePedido', sendData, {
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
                }

                vm.ActualizarPedido();
            });
        }

        vm.facturarCredito = function () {
            vm.DatosFacturacion.id_factura = vm.nPedido;
            vm.DatosFacturacion.tipo_pago = 1;
            vm.DatosFacturacion.credito_fiscal = false;
            vm.DatosFacturacion.monto_credito = vm.Total;
            if (confirm("Esta seguro que desea facturar al crédito?")) {
                $http.post('/facturacion/factura/Facturar', vm.DatosFacturacion, {
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
                        if (response.data.FacturarResult > 0) {
                            notificationService.notifySuccess("Pedido facturado con éxito");
                            vm.imprimirFactura();
                            location.href = location.href;
                        } else {
                            notificationService.notifyError("Ha ocurrido un error inesperado");
                        }
                    }
                });
            }
        }

        vm.facturarContado = function () {
            vm.DatosFacturacion.id_factura = vm.nPedido;
            vm.DatosFacturacion.tipo_pago = 0;
            vm.DatosFacturacion.credito_fiscal = false;
            vm.DatosFacturacion.monto_en_efectivo = vm.Total;
            if (confirm("Esta seguro que desea facturar al crédito?")) {
                $http.post('/facturacion/factura/Facturar', vm.DatosFacturacion, {
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
                        if (response.data.FacturarResult > 0) {
                            notificationService.notifySuccess("Pedido facturado con éxito");
                            vm.imprimirFactura();
                            location.href = location.href;
                        } else {
                            notificationService.notifyError("Ha ocurrido un error inesperado");
                        }
                    }
                });
            }
        }

        vm.setMostrarFormaPago = function () {
            vm.MostrarFormaPago = true;
        }

        vm.ReservarPedido = function () {
            if (vm.PedidoActivo) {
                if (confirm("Esta seguro que desea realizar esta reserva?")) {
                    $http.post('/facturacion/factura/ReservarPedido', { id: vm.idPedido }, {
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
                            notificationService.notifySuccess("Pedido reservado con éxito!");
                        }
                    });
                }
            } else {
                notificationService.notifyError("No se ha cargado ningún pedido");
            }
        }

        vm.imprimirFactura = function () {
            var url = vm.ReportServerURL + '?/Facturacion/facturacomercial&rs:Command=Render&id_factura=' + vm.idPedido;
            window.open(url, '_blank');
        }

        function actualizarDatosPedido(pedido) {
            var UpdateUri = '/facturacion/factura/CargarDetallePedido?idPedido=' + vm.idPedido;
            var grid = Sigma.$grid("factura_grid");
            grid.loadURL = UpdateUri;
            grid.reload();

            vm.SubTotal = pedido.subtotal;
            vm.Iva = pedido.iva;
            vm.Total = pedido.total;
            vm.Descuento = pedido.descuento;
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

            vm.MontoPagoEfectivo = 0.0;

            vm.Flete = false;
            vm.CF = false;

            vm.MostrarSelector = false;
            vm.MostrarFormaPago = false;

            vm.bLinea = null;
            vm.bEstilo = null;
            vm.bColor = null;
            vm.bTalla = null;

            vm.DatosFacturacion = {
                id_factura: 0
                , tipo_pago: 0
                , credito_fiscal: false
                , id_boleta_pago: 0
                , monto_en_tarjeta: 0.0
                , monto_en_efectivo: 0.0
                , monto_por_deposito: 0.0
                , monto_por_cheque: 0.0
                , monto_credito: 0.0
            };
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
                        vm.nPedido = response.data.serial_number;
                        vm.idPedido = response.data.id_pedido;
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
            if (x === null) return "";
            return x.replace(/^\s+|\s+$/gm, '');
        }

    };

})();
