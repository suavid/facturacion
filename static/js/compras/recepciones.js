var recepcionApp = angular.module('recepcionApp', []);

recepcionApp.controller("solicitudesCtrl", function($scope, $http){
	$scope.solicitudesPendientes = [];

	var uri  = "/compras/compras/get_solicitudes_aprobadas";

	$http.post(uri, { }).success(function(response){

		$scope.solicitudesPendientes = response;
	
	});
	
});

