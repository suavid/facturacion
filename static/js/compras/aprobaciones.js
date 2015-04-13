var aprobacionApp = angular.module('aprobacionApp', []);

aprobacionApp.controller("solicitudesCtrl", function($scope, $http){
	$scope.solicitudesPendientes = [];

	var uri  = "/compras/compras/get_solicitudes";

	$http.post(uri, { }).success(function(response){

		$scope.solicitudesPendientes = response;
	
	});
	
});

