<?php
	//Initialize session
	session_start();
	$langcode = "de";
	$_SESSION[$langcode."previousNoun"] = $_SESSION[$langcode."noun"];
	$_SESSION[$langcode."previousArticle"] = $_SESSION[$langcode."article"];
	$_SESSION[$langcode."previousAnswer"] = $_SESSION[$langcode."answer"];
	$_SESSION[$langcode."previousGuess"] = $_SESSION[$langcode."guess"];
	
	if($_SESSION[$langcode."points"]=="") {$_SESSION[$langcode."points"]=0;}
	if($_SESSION[$langcode."round"]=="") {$_SESSION[$langcode."round"]=1;}
	
	//Query
	$sparqlQuery = '
	SELECT ?lemma (SAMPLE(?gender) AS ?gender) WITH {
	  SELECT DISTINCT ?lemma WHERE {
	    VALUES ?gender { wd:Q499327 wd:Q1775415 wd:Q1775461 }
	    ?lexeme dct:language wd:Q188;
	            wikibase:lexicalCategory wd:Q1084;
	            wdt:P5185 ?gender;
	            wikibase:lemma ?lemma.
	  }
	  ORDER BY CONCAT(MD5(?lemma), STR(NOW()))
	} AS %randomLemmas WHERE {
	  INCLUDE %randomLemmas.
	  ?lexeme wikibase:lemma ?lemma;
	          wdt:P5185 ?gender.
	}
	GROUP BY ?lemma
	HAVING(COUNT(?gender) = 1)';

	//get result of the query in json
	$result = file_get_contents('https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query='.urlencode($sparqlQuery));

	
	//parse the query
	try { $items = json_decode($result); }
		catch ( Exception $e ) {
		echo '<p>Problem, no results available.</p>';
	}
		
		if (count($items->results->bindings) == 0) {
			echo '<p>No results.</p>';
	    }
	    else {
    		//select a random lexeme among the results of the query
			$random=rand(0, 200);
			$line=0;
			foreach ($items->results->bindings as $item) {
				$line++;
				if ($line==$random&&$item->lemma->value!=$_SESSION[$langcode."previousNoun"])
				{
					break;
				}
			}
		}
	//convert Qitem of gender into the article (simple version)
	switch($item->gender->value) {
		case "http://www.wikidata.org/entity/Q499327": {$_SESSION[$langcode."article"]="der";break;}
		case "http://www.wikidata.org/entity/Q1775415": {$_SESSION[$langcode."article"]="die";break;}
		case "http://www.wikidata.org/entity/Q1775461": {$_SESSION[$langcode."article"]="das";break;}
		}
		
	$_SESSION[$langcode."noun"]=$item->lemma->value;

	// Check the answer and give feedback
	if (isset($_POST['article'])) 
		{ 
		$_SESSION[$langcode."answer"]=$_POST['article'];
		
		
		//First round
		if($_SESSION[$langcode."answer"]=='') {
			$_SESSION[$langcode."previousGuess"]='';
			$_SESSION[$langcode."image"]='';
		}
		
		//I don't know
		if($_SESSION[$langcode."answer"]=='idk') {
			 $_SESSION[$langcode."previousGuess"]='(0)';
			$_SESSION[$langcode."image"]='zero';
			 
		}
		
		//Good guess
		elseif($_SESSION[$langcode."answer"]==$_SESSION[$langcode."previousArticle"]) {
			$_SESSION[$langcode."points"]++;
			 $_SESSION[$langcode."previousGuess"]='(+1)';
			$_SESSION[$langcode."image"]='plus';
			}
			
		//Wrong guess
			else {
			//positive encouragement gameplay: no loss of point for a wrong answer
			$_SESSION[$langcode."previousGuess"]='(0)';
			$_SESSION[$langcode."image"]='zero';

			//aggressive gameplay: negative point for a wrong answer
			//$_SESSION[$langcode."points"]=$_SESSION[$langcode."points"];
			//$_SESSION[$langcode."previousGuess"]='(-1)';
			//$_SESSION[$langcode."image"]='minus';
				}
				
		//Increment round, check if it's the end
		$_SESSION[$langcode."round"]++;
		if($_SESSION[$langcode."round"]==11) { $phase='end';}
		else {$phase='game';}
		
		//Add the previous word, its article and the points into the list
		$_SESSION[$langcode."list"]='<p class="list '.$_SESSION[$langcode."image"].'">'.' '.$_SESSION[$langcode."previousArticle"].' '.$_SESSION[$langcode."previousNoun"].'</p>'.$_SESSION[$langcode."list"];
		}
	?>
