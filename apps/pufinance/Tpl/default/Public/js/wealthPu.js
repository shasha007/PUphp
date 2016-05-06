var pu=angular.module("wealthPu",["ui.router"]);
pu.run(function($rootScope){
	$rootScope.bar="还款";
	$rootScope.setBg=false;
});
pu.config(function($stateProvider,$urlRouterProvider){
	$urlRouterProvider.when("","/punav");
	$stateProvider
		.state("punav",{
			url:"/punav",
			templateUrl:"puNav.html",
			controller:"navCtrl"
		})
		.state("repaySubmit",{
			url:"/repaySubmit",
			templateUrl:"repayMentSubmit.html",
			controller:"submitCtrl"
		})
		.state("repaySuccess",{
			url:"/repaySuccess",
			templateUrl:"repaySuccess.html",
			controller:"repaySuccessCtrl"
		})
});
pu.controller("navCtrl",["$scope","$rootScope",function($scope,$rootScope){
	$rootScope.bar="天平";
}]);
pu.controller("repayCtrl",["$scope","$rootScope",function($scope,$rootScope){
	
}]);
pu.controller("submitCtrl",["$scope","$rootScope",function($scope,$rootScope){
	$rootScope.bar="支付";
	$rootScope.setBg=true;
}]);
pu.controller("repaySuccessCtrl",["$scope","$rootScope",function($scope,$rootScope){
	$rootScope.bar="";
	$rootScope.setBg=false;
}]);
























