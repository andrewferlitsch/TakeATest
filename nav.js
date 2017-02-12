technical.controller( 'navCtrl', function( $scope ) {
})
.directive( "nav", function() {
	return {
		restrict: 'A',
		template: "<div style='text-align: center'>" +
				  "<h1>The Technical Interview</h1>" +
				  "</div>" +
				  "<ul class='w3-navbar w3-green'>" +
				  "	<li><a href='/'>Flashcard</a></li>" +
				  "	<li><a href='coding-examples.html'>Coding</a></li>" +
				  "	<li><a href='preparing-for-technical-interview.html'>Advice</a></li>" +
				  "<span style='font-size: 9pt' class='w3-right'>Questions: <span class='w3-badge w3-tiny w3-teal'>700</span></span>" +
				  "</ul>" +
				  "<br/>"
	}
});