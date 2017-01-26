technical.controller( 'dataScienceCtrl', function( $scope, $http, $location, $anchorScroll ) {
	$scope.subject 	= "Data Science";
	$scope.name  	= "datascience";
	
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
	$scope.random 	= $scope.questions;
	$scope.show 	= false;
	$scope.answers 	= false;

	$scope.scrollTo = function() {
      $location.hash( $scope.name );
      $anchorScroll();
	  
	  if ( $scope.show ) {
		$http({
			method : "GET",
			url : "load.php",
			params: { category: $scope.subject }
		}).then(function mySucces(response) {
			$scope.questions = response.data;
			$scope.random 	 = pick3( $scope.questions );
			}, function myError(response) {
		});
	  }
    }
	
	$scope.better = function( id ) {
		showBetter( $scope.subject, id );
	}
	$scope.rank = function( id ) {
		showRank( $scope.subject, id );
	}
	$scope.suggest = function() {
		showSuggest( $scope.subject );
	}

	$scope.scored = false;
	$scope.score_css = "w3-green";
	$scope.score_txt = "Score";
	$scope.score = function() {
		if ( ( $scope.scored = !$scope.scored ) ) {
			Tally( $scope.name );
			$scope.score_css = "w3-red";
			$scope.score_txt = "Scored";
		}
		else {
			UnTally( $scope.name );
			$scope.score_css = "w3-green";
			$scope.score_txt = "Score";
		}
	}
})
.directive( "questionsDataScience", function() {
	return {
		restrict: 'A',
		templateUrl: 'flip.html'
	}
});