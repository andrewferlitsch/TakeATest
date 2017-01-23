technical.controller( 'rCtrl', function( $scope, $http, $location, $anchorScroll ) {
	$scope.subject = "R";
	$scope.name    = "r";
	
	$scope.questions = [{ question: "placeholder 1",
						  answer: "",
						  rank: 1,
						  id: 1
						},
						{ question: "placeholder 2",
						  answer: "",
						  rank: 2,
						  id: 2
						},
						{ question: "placeholder 3",
						  answer: "",
						  rank: 3,
						  id: 3
						}
					  ];
	$scope.random 	= pick3( $scope.questions );
	$scope.show 	= false;
	$scope.answers 	= false;
	$http({
        method : "GET",
        url : "load.php",
		params: { category: $scope.subject }
    }).then(function mySucces(response) {
        $scope.questions = response.data;
		$scope.random 	 = pick3( $scope.questions );
    }, function myError(response) {
    });
    
	$scope.better = function( id ) {
		showBetter( $scope.subject, id );
	}
	$scope.rank = function( id ) {
		showRank( $scope.subject, id );
	} 
	$scope.suggest = function() {
		showSuggest( $scope.subject );
	}
	$scope.scrollTo = function() {
      $location.hash( $scope.name );
      $anchorScroll();
    }
	$scope.scrollTo = function() {
      $location.hash( $scope.name );
      $anchorScroll();
    }
})
.directive( "questionsR", function() {
	return {
		restrict: 'A',
		templateUrl: 'qa.html'
	}
});