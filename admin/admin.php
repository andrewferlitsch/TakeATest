<?php
/* Copyright(c), 2016-2017, Andrew Ferlitsch, All Rights Reserved
 */
session_start();
include_once "../db.php";
?>
<html lang="en-US">
<head>
	<title>Quisse - Admin Dashboard</title>
	<meta NAME="robots" CONTENT="noindex, nofollow">
	<meta charset="utf-8">
	<meta name='keywords' content=''>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="http://www.w3schools.com/lib/w3.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<style>
	body { padding: 10px; }
	h2 { color: steelblue; }
	.error { weight: bold; color: red; }
	.ok { weight: bold; color: green; }
	</style>
<script>
$(function() {
	var category;	// selected category
	var questions;	// list of questions
	var index;		// index into list of questions
	var size;		// size of question index

	// Select a category
	$("#category").change( function() {
		ClearStatus();
		category = this.value;
		if ( category == "" )
			return;
		$.get("/admin/get.php?category=" + category.replace( /\+/g, "%2B").replace( /#/g, "%23"), function( data, status ) {
			data = data.replace(/(\r\n|\n)/g, '\\n');
			questions = JSON.parse( data );
			index = 0;
			size  = questions.length;
			$("#size").html( size );
			
			// populate the first question
			Populate();
		})
		.fail (function( response ) {
			$("#err-cat").html( "Get Categories Failed: errCode = " + response.status );
		});	 
	})
	
	// Goto previous question in category
	$("#prev").click( function() {
		ClearStatus();
		if ( index == 0 )
			index = size - 1;
		else
			index--;
		Populate();
	})
	
	// Goto next question in category
	$("#next").click( function() {
		ClearStatus();
		if ( index == size - 1 )
			index = 0;
		else
			index++;
		Populate();
	})
	
	// Populate input form
	function Populate() {
		var question  = questions[ index ];
		$("#id").html( question.id );
		$("#question").val( question.question );
		$("#answer").val( question.answer );
		$("#rank").val( question.rank );
		$("#level").val( question.level );
		$("#tcount").html( question.tcount );
		var timing = 0;
		if ( question.tcount > 0 )
			timing = question.timing / question.tcount;
		
		$("#timing").html( timing .toFixed(2));
		$("#words").html( question.words );
		$("#wordsan").html( question.wordsan );
		$("#submit").val( "Update" );
				
		$("#err-sim").html( "" );
		$("#similar").html( "" );
		var similar = question.similar.split( "," );
		if ( question.similar != "" ) {
			for ( var i = 0; i < similar.length; i++ ) {
				var pair = similar[ i ].split(":");
				$.get("/admin/get.php?id=" + pair[ 1 ], function( data, status ) {
					data = data.replace(/(\r\n|\n)/g, '\\n');
					var question = JSON.parse( data );
					var answer   = question.answer;
					var id       = question.id;
					$("#similar").html( $("#similar").html() + id + ":" + answer + "\n" );
				})
				.fail (function( response ) {
					$("#err-sim").html( "Get Question Failed: errCode = " + response.status );
				});	 
			}
		}
	}
	
	// Clear input form to enter new question
	$("#new").click( function() {
		ClearStatus();
		$("#id").html( "" );
		$("#question").val( "" );
		$("#answer").val( "" );
		$("#rank").val( 1 );
		$("#level").val( "" );
		$("#words").html( "" );
		$("#wordsan").html( "" );
		$("#similar").html( "" );
		$("#tcount").html( "" );
		$("#timing").html( "" );
		$("#submit").val( "Add" );
	})
	
	// Submit new or updated question
	$("#submit").click( function() {
		var action;
		
		// Add a question
		if ( $(this).val() == "Add" ) {
			action = "add";
			$("#submit").val( "Update" );
			
			// increment the list of questions by one
			index = size;
			$("#size").html( ++size );
			questions.push( { question: "", answer: "", level: "" } );
		}
		// Update a question
		else {
			action = "update";
		}
	
		$("#ok-sub").html( "" );
		$("#err-sub").html( "" );
		$.post( "/admin/post.php",
			{ action  : action,
			  category: category,
			  id      : $("#id").html(),
			  question: $("#question").val(),
			  answer  : $("#answer").val(),
			  rank    : $("#rank").val()
			},
			function ( data, status ) {
				questions[ index ].question = $("#question").val();
				questions[ index ].answer   = $("#answer").val();
				questions[ index ].rank     = $("#rank").val();
				if ( action == "add" ) {
					$("#id").html( data );
					questions[ index ].id = data;
				}
				$("#ok-sub").html( "Done" );
			}
		)
		.fail (function( response ) {
			$("#err-sub").html( "Unable to Add/Update: errCode = " + response.status );
		});	 
	})
	
	// Refresh the bag of words [ REDO]
	$("#refresh-words").click( function() {
		$("#ok-red").html( "" );
		$("#err-red").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "reduce",
			  id      : $("#id").html()
			},
			function ( data, status ) {
				var tokens = data.split("|");
				$("#words").html( tokens[ 0 ] );
				questions[ index ].words = tokens[ 0 ];
				$("#wordsan").html( tokens[ 1 ] );
				questions[ index ].wordsan = tokens[ 1 ];
				$("#ok-red").html( "Done" );
			}
		)
		.fail (function( response ) {
			$("#err-red").html( "Unable to Reduce Question: errCode = " + response.status );
		});	 
	})
	
	// Refresh the list of similar questions
	$("#refresh-similar").click( function() {
		$("#ok-sim").html( "" );
		$("#err-sim").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "similar",
			  id      : $("#id").html()
			},
			function ( data, status ) {
				$("#similar").html( data );
				questions[ index ].similar = data;
				$("#ok-sim").html( "Done" );
			}
		)
		.fail (function( response ) {
			$("#err-sim").html( "Unable to find Similar Questions: errCode = " + response.status );
		});	 
	})
	
	function ClearStatus() {
		$("#ok-sub").html( "" );
		$("#err-sub").html( "" );
		$("#ok-red").html( "" );
		$("#err-red").html( "" );
		$("#ok-sim").html( "" );
		$("#err-sim").html( "" );
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
	}
	
	$("#reduce-all").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "reduce-all"
			},
			function ( data, status ) {
				$("#ok-all").html( data );
			}
		)
		.fail (function( response ) {
			$("#err-all").html( "Unable to Reduce All: errCode = " + response.status );
		});	 
	})
	
	$("#reduce-cat").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "reduce-cat",
			  category: category
			},
			function ( data, status ) {
				$("#ok-all").html( data );
			}
		)
		.fail (function( response ) {
			$("#err-all").html( "Unable to Reduce Category: errCode = " + response.status );
		});	 
	})
	
	$("#similar-all").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "similar-all"
			},
			function ( data, status ) {
				$("#ok-all").html( data );
			}
		)
		.fail (function( response ) {
			$("#err-all").html( "Unable to find Similar All: errCode = " + response.status );
		});	 
	})
	
	$("#similar-cat").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		$.post( "/admin/nlp.php",
			{ action  : "similar-cat",
			  category: category
			},
			function ( data, status ) {
				$("#ok-all").html( data );
			}
		)
		.fail (function( response ) {
			$("#err-all").html( "Unable to find Similar Category: errCode = " + response.status );
		});	 
	})
	
	$("#timing-scan").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		$.post( "/admin/timing.php",
			{ action  : "scan",
			  category: category,
			},
			function ( data, status ) {
				$("#ok-all").html( data );
			}
		)
		.fail (function( response ) {
			$("#err-all").html( "Unable to Timing: errCode = " + response.status );
		});	 
	})
	
	$("#timing-level").click( function() {
		$("#ok-all").html( "" );
		$("#err-all").html( "" );
		
			$.post( "/admin/timing.php",
				{ action  : "level"
				},
				function ( data, status ) {
					res = data.split( "," );
					$("#ok-all").html( "AVE " + res[ 0 ] + " MAX " + res[ 1 ] + " MAX-AVE " + res[ 2 ] );
				}
			)
			.fail (function( response ) {
				$("#err-all").html( "Unable to Timing: errCode = " + response.status );
			});	 
	})
})
</script>
</head>
<body ng-app="technical">

	<?php include "adminlogin.php"; ?>
	
	<header ng-controller="navCtrl" style='text-align:center'>
		<div nav></div>
		<h2>Quisse Admin Dashboard</h2>
	</header>
	
	<section id='authenticated'>
	
		<section class='w3-container'>
			<!-- category -->
			<label for='category' class='w3-label'>Category</label>
			<select id='category' name='category' class='w3-input' required>
				<option value='' disabled selected>Select a category...</option>
				<?php $categories = $db->GetCategories();
				$ncat = count( $categories );
				for ( $i = 0; $i < $ncat; $i++ ) {
					$category = $categories[ $i ];
					echo "<option id='$category'>$category</option>";
				}
				?>
			</select>
			<span id='err-cat' class='error'></span>

			<!-- ID -->
			<label for='id' class='w3-label'>ID:</label>
			<span id='id' name='id'></span>&nbsp;
			<!-- No of Questions -->
			<label for='size' class='w3-label'>No. of Questions:</label>
			<span id='size' name='size'></span>&nbsp;
			<!-- total responses -->
			<label for='tcount' class='w3-label'>Response Count:</label>
			<span id='tcount' name='tcount'></span>&nbsp;
			<!-- Ave Timing -->
			<label for='timing' class='w3-label'>Ave Timing:</label>
			<span id='timing' name='timing'></span><br/><br/>

			<!-- Next, Prev, New -->
			<button id='prev' class='w3-btn w3-blue'>Prev</button>
			<button id='next' class='w3-btn w3-blue'>Next</button>
			<button id='new'  class='w3-btn w3-blue'>New</button>
			<br/><br/>

			<!-- Question -->
			<label for='question' class='w3-label'>Question:</label>
			<input type='text' id='question' name='question' required=true class='w3-input'/>
			
			<!-- Answer -->
			<label for='answer' class='w3-label'>Answer:</label>
			<textarea id='answer' name='answer' cols=40 rows=3 required=true class='w3-input'>
			</textarea>
			
			<!-- Rank -->
			<label for='rank' class='w3-label'>Level (Original)</label>
			<select id='rank' name='rank' class='w3-input'>
				<option value='1'>1</option>
				<option value='2'>2</option>
				<option value='3'>3</option>
			</select>
			
			<label for='level' class='w3-label'>Level (Learned)</label>
			<input id='level' name='level' class='w3-input' readonly/>
			<br/>
			
			<!-- Submit -->
			<input type='submit' id='submit' value='Update'/>
			<span id='ok-sub' class='ok'></span><br/>
			<span id='err-sub' class='error'></span><br/>
			
			<!-- Bag of Words -->
			<label for='words' class='w3-label'>Bag of Words</label>
			<button id='refresh-words' class='w3-btn w3-blue w3-small'>Refresh</button>
			<span id='ok-red' class='ok'></span>
			<span id='err-red' class='error'></span>
			<pre name='words' id='words'></pre>
			<pre name='wordsan' id='wordsan'></pre>
			
			<!-- Similar Matching Questions -->
			<label for='similar' class='w3-label'>Similar Questions</label>
			<button id='refresh-similar' class='w3-btn w3-blue w3-small'>Refresh</button>
			<span id='ok-sim' class='ok'></span>
			<span id='err-sim' class='error'></span>
			<textarea name='similar' id='similar' class='w3-input' rows=4></textarea>
		</section>
		<hr/>
		
		<section class='w3-container'>
			<button id='reduce-cat' class='w3-btn w3-blue w3-small'>Reduce Category</button>
			<button id='reduce-all' class='w3-btn w3-teal w3-small'>Reduce All</button>
			<button id='similar-cat' class='w3-btn w3-blue w3-small'>Similar Category</button>
			<button id='similar-all' class='w3-btn w3-teal w3-small'>Similar All</button>
			<button id='timing-scan' class='w3-btn w3-green w3-small'>Scan Timing Data</button>
			<button id='timing-level' class='w3-btn w3-blue w3-small'>Adjust Levels</button>
			<br/>
			<span id='ok-all' class='ok'></span>
			<span id='err-all' class='error'></span><br/><br/>
		</section>
	
	</section>

	<footer ng-controller="footerCtrl" id='footer'>
			<div footer></div>
	</footer>

<script>
var technical = angular.module( 'technical', []);
</script>
<script src="../nav.js"></script>
<script src="../footer.js"></script>
</body>
</html>