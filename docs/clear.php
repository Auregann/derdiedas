<?php
	session_start();
	$_SESSION["previousNoun"] = $_SESSION["noun"];
	$_SESSION["previousArticle"] = $_SESSION["article"];
	$_SESSION["previousAnswer"] = $_SESSION["answer"];
	if($_SESSION["points"]=="") {$_SESSION["points"]=0;}
	$endpointUrl = 'https://query.wikidata.org/sparql';
	$sparqlQuery = <<< 'SPARQL'
	SELECT ?lemma (SAMPLE(?gender) AS ?gender) WITH {
	  SELECT DISTINCT ?lemma WHERE {
	    VALUES ?gender { wd:Q499327 wd:Q1775415 wd:Q1775461 }
	    ?lexeme dct:language wd:Q188;
	            wdt:P5185 ?gender;
	            wikibase:lemma ?lemma.
	  }
	  ORDER BY CONCAT(MD5(?lemma), STR(NOW()))
	  LIMIT 100
	} AS %randomLemmas WHERE {
	  INCLUDE %randomLemmas.
	  ?lexeme wikibase:lemma ?lemma;
	          wdt:P5185 ?gender.
	}
	GROUP BY ?lemma
	HAVING(COUNT(?gender) = 1)
	SPARQL;

	class sparql {
	    
	    private static $queries = array();
	    
	    private static function getCacheFilename($query) {
	        return SPARQL_CACHE_DIR.md5($query).'.dat';
	    }
	    
	    public static function query($query, $cache = 0) {
	        self::$queries[] = $query;
	        $cacheFilename = self::getCacheFilename($query);
	        clearstatcache();
	        if (($cache > 0) && file_exists($cacheFilename) && (filemtime($cacheFilename) >= time() - $cache)) {
	            $data = @file_get_contents($cacheFilename);
	        } else {
	            $data = @file_get_contents('https://query.wikidata.org/bigdata/namespace/wdq/sparql?format=json&query='.urlencode($query));
	            if ($data === false) {
	                throw new Exception('Erreur à l\'exécution de la requête SPARQL.'."\n".$query);
	            }
	            if ($cache > 0) {
	                file_put_contents($cacheFilename, $data);
	            }
	        }
	        return json_decode($data);
	    }
	    
	    public static function getQueryTime($query) {
	        $cacheFilename = self::getCacheFilename($query);
	        clearstatcache();
	        if (file_exists($cacheFilename)) {
	            return filemtime($cacheFilename);
	        }
	        return null;
	    }
	    
	    public static function getQueries() {
	        return self::$queries;
	    }

	}
?>




--------------------


 <?php

try { $items = sparql::query($sparqlQuery); }
	catch ( Exception $e ) {
	echo '<p>Problem, no results available.</p>';
}
	
	if (count($items->results->bindings) === 0) {
		echo '<p>No results.</p>';
    }
    else {
		
			$random=rand(0, 99);
			$line=0;
			foreach ($items->results->bindings as $item) {
				$line++;
				if ($line==$random)
				{
					break;
				}
		}
		
				
		switch($item->gender->value) {
			case "http://www.wikidata.org/entity/Q499327": {$article="der";break;}
			case "http://www.wikidata.org/entity/Q1775415": {$article="die";break;}
			case "http://www.wikidata.org/entity/Q1775461": {$article="das";break;}
			}
		$noun=$item->lemma->value;
	?>
	<div>
	<?php
	// Check the answer and give feedback
	if (isset($_POST['article'])) 
				{ 
				$_SESSION["answer"]=$_POST['article'];
				
				//I don't know
				if($_SESSION["answer"]=='idk') {
					 echo  'The correct answer was: '.$_SESSION["previousArticle"].' '.$_SESSION["previousNoun"];
				}
				
				//Good guess
				elseif($_SESSION["answer"]==$_SESSION["previousArticle"]) {
					echo 'Yay! The correct answer was: '.$_SESSION["previousArticle"].' '.$_SESSION["previousNoun"];
					$_SESSION["points"]++;
					}
					
				//Wrong guess
					else {
					echo 'Oh noes :( The correct answer was: '.$_SESSION["previousArticle"].' '.$_SESSION["previousNoun"];
						$_SESSION["points"]=$_SESSION["points"]-1;
						}
					
				$_SESSION["round"]++;
				if($_SESSION["round"]==10) { $phase='end';}
				else {$phase='game';}
					
				$_SESSION["noun"]=$noun;
				$_SESSION["article"]=$article;
				$_SESSION["list"]=$_SESSION["previousArticle"].' '.$_SESSION["previousNoun"].'<br/>'.$_SESSION["list"];
				}

	?>
</div>
<div>
		<p>
			<?php echo $noun.' ('.$article.')<br/>';?>
		</p>
</div>
<div>
		<form method="post" action="index.php">
		<input type="radio" id="der" name="article" value="der" /> <label for="der">der</label>
		<input type="radio" id="die" name="article" value="die" /> <label for="die">die</label>
		<input type="radio" id="das" name="article" value="das" /> <label for="das">das</label>
		<input type="radio" id="idk" name="article" value="idk" checked="checked"/> <label for="idk">I don\'t know</label>
		<br/>
		<input type="submit" value="Submit" />
	</form>
</div>
<div>
			<p>Previously: <br/>
			<?php echo $_SESSION["list"];?>
			</p>
			<p>Round: <?php echo $_SESSION["round"];?>/10</p>
			<p>Points: <?php echo $_SESSION["points"];?></p>
			
			<?php 
			if($phase=='end')
			{session_unset();
			}
			?>
		</div>
		<p>Variables de test:</p>
		<?php echo 
			'<p>State: '.$phase.'</p>'.
			'<p>Answer: '.$_SESSION["answer"].'</p>';
			?>
	