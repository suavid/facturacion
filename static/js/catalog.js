var catalogInfo = angular.module('catalogInfo', []);

catalogInfo.controller('catalogCtrl', function($scope, $http){
	
	$scope.lineas = [];
	$scope.colores = [];
	$scope.tallas = [];
	$scope.linea  = "";
	$scope.estilo  = "";	
	$scope.color = "";
	$scope.talla = "";
	$scope.costoUnitario = 0;
	$scope.cantidad = 0;

	$http.post('/inventario/inventario/obtener_lineas',{}).success(function(resp){
		$scope.lineas = resp;
		$scope.linea  = 1;
		$('#bLinea option[value="0"]').remove();
	});

	$scope.cargarColores = function(){
		var data = {"linea": $scope.linea, "estilo": $scope.estilo};

		$http.post('/inventario/inventario/obtener_colores_por_estilo',data).success(function(resp){
			if(resp.length > 0){
				$scope.colores = resp;
				$scope.color  = resp[0].id;
				$('#bColor option[value="0"]').remove();
				$scope.cargarCorrida();
			}else{
				$scope.colores = [];
				$scope.color = 0;
				$('#Enter').attr("disabled", "disabled");
				$scope.cantidad = 0;
				$scope.costoUnitario = 0;
			}
		});

	};

	$scope.cargarCorrida = function(){
		var data = {"linea": $scope.linea, "estilo": $scope.estilo, "color": $scope.color};

		$http.post('/inventario/inventario/obtener_tallas_por_estilo',data).success(function(resp){
			if(resp.length > 0){
				$scope.tallas = resp;
				$scope.talla  = resp[0].talla;
				$('#bTalla option[value="0"]').remove();
			}else{
				$scope.tallas = [];
				$scope.talla = 0;
			}
		});

	};

	$scope.soloNumero = function(prop){
		if(isNaN($scope[prop])){
			$scope[prop] = 0;
		}
	};

	$scope.validarProducto = function(){
		if($scope.lineas.length > 0 && $scope.colores.length > 0 && $scope.tallas.length > 0 && $scope.estilo != "" && $scope.costoUnitario > 0 && $scope.cantidad > 0 && $scope.color != 0 && $scope.talla != 0){
			$('#Enter').removeAttr("disabled");
		}else{
			$('#Enter').attr("disabled", "disabled");
		}
	}

	$scope.seleccionarProducto = function(){
		var producto = {
			"linea": $scope.linea,
			"estilo": $scope.estilo,
			"color": $scope.color,
			"talla": $scope.talla,
			"cantidad": $scope.cantidad,
			"costo": $scope.costoUnitario
		};

		$scope.$parent.producto = producto;
		$scope.$parent.procesarProducto();

		$scope.colores = [];
		$scope.tallas = [];
		$scope.linea  = "";
		$scope.estilo  = "";	
		$scope.color = "";
		$scope.talla = "";
		$scope.costoUnitario = 0;
		$scope.cantidad = 0;

		$scope.validarProducto();

	};

});