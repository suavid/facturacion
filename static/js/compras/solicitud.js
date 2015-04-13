var comprasApp = angular.module('comprasApp', ['catalogInfo']);

comprasApp.controller("nuevaSolicitudCtrl", function($scope, $http){
	
	var f = new Date();

	$scope.idSolicitud = 0;
	$scope.proveedor = "";
	$scope.fecha = f.getFullYear()+ "-" + (f.getMonth()+1) + "-" + f.getDate();
	$scope.observaciones = "";
	$scope.total = 0;
	$scope.total_costo = "0.0";
	$scope.validForm = false;
	$scope.editable = 0;
	$scope.nombre_proveedor = "SELECCIONE UN PROVEEDOR";
	$scope.productos_proveedor = [];

	$scope.producto = null;

	$scope.solicitudActiva = false;

	$scope.proveedorError = false;
	$scope.fechaError = false;
	$scope.observacionesError = false;
	$scope.errorForm = false;


	$scope.crearSolicitud = function(){
		$scope.validForm = true;

		var uri  = "/compras/compras/crear_solicitud";
		
		var data = {
			"id": $scope.idSolicitud,
			"proveedor": $scope.proveedor,
			"fecha": $scope.fecha,
			"observaciones": $scope.observaciones,
			"total": $scope.total,
			"total_costo": $scope.total_costo
		};

		if($scope.proveedor == ""){
			$scope.proveedorError = true;
			$scope.validForm = false;
			$scope.errorForm = true;
		}else{
			$scope.proveedorError = false;
			$scope.validForm = true && $scope.validForm;
			$scope.errorForm = false || $scope.errorForm;
		}

		if($scope.fecha == ""){
			$scope.fechaError = true;
			$scope.validForm = false;
			$scope.errorForm = true;
		}else{
			$scope.fechaError = false;
			$scope.validForm = true && $scope.validForm;
			$scope.errorForm = false || $scope.errorForm;
		}

		if($scope.observaciones == ""){
			$scope.observacionesError = true;
			$scope.validForm = false;
			$scope.errorForm = true;
		}else{
			$scope.observacionesError = false;
			$scope.validForm = true && $scope.validForm;
			$scope.errorForm = false || $scope.errorForm;
		}

		if($scope.validForm){
			$scope.observacionesError = false;
			$scope.fechaError = false;
			$scope.proveedorError = false;
			$scope.errorForm = false;

			$http.post(uri, data).success(function(response){
				
				$scope.idSolicitud = response.id;
				if($scope.idSolicitud){
					$scope.solicitudActiva = true;
				}

				$scope.cargarSolicitud();

				$('#btnproveedor').attr('disabled', 'disabled');
        		$('#observaciones').attr('disabled', 'disabled');
			});
			
		}

	};

	$scope.cargarSolicitud = function(){

		var uri  = "/compras/compras/solicitud_data";
       
        var data = {
            "id_orden": $scope.idSolicitud
        };

        $http.post(uri, data).success(function(resp){
        	$scope.solicitudActiva = true;
            $scope.fecha = resp[0].fecha;
            $scope.proveedor = resp[0].proveedor;
            $scope.total_costo = resp[0].total_costo;
            $scope.total = resp[0].total;
            $scope.observaciones = resp[0].observaciones;
            $scope.editable = resp[0].editable;
        
        });

	};

	$scope.confirmar = function(){

		var est = confirm("Desea confirmar este documento?");
		
		if(est)
		{
			if($scope.total <= 0 || $scope.total_costo <= 0){

				alert("No se permite confirmar solicitudes vacías");

			}else{
				$http.post('/compras/compras/confirmar_solicitud',{"id_solicitud": $scope.idSolicitud}).success(function(resp){
					$scope.editable = 0;
					alert("Documento confirmado con éxito");
				});			
			}
		}
	}

	$scope.$watch('proveedor', function(){
		$http.post('/compras/compras/nombre_proveedor',{"proveedor": $scope.proveedor}).success(function(resp){
			$scope.nombre_proveedor = resp.nombre;
		});
		$http.post('/compras/compras/productos_proveedor',{"proveedor": $scope.proveedor}).success(function(resp){
			$scope.productos_proveedor = resp;
			//alert(resp);
		});
	});

	$scope.procesarProducto = function(){

		if($scope.solicitudActiva){

			var data = $scope.producto;

			data['id_orden'] = $scope.idSolicitud;

			$http.post('/compras/compras/agregar_item',data).success(function(resp){
				
				var grid = Sigma.$grid("detalle_grid");
	        	grid.loadURL = '/compras/grid_tables/detalle_solicitud_grid_1/' + $scope.idSolicitud;
	        	grid.reload();

	        	if(resp.exito){
		        	$scope.total_costo = parseFloat($scope.total_costo) + parseFloat(data.cantidad * data.costo);
					$scope.total = parseFloat($scope.total) + parseFloat(data.cantidad);
					
					var info = {"id":$scope.idSolicitud, "total":data.cantidad, "total_costo": (data.cantidad * data.costo)};
					
					$http.post('/compras/compras/actualizar_solicitud',info).success(function(resp){
						// nnd
						// nnd
					});
				}

			});
		}
	};

});

comprasApp.directive('productPicker', function(){
	return {
		templateUrl: '../static/html/default/templates/compras/chuncks/product-picker.html'
	};
});