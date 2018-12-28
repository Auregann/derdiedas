<?php
	require('docs/lib.php');
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head><meta name='robots' content=''>
		<title>DerDieDas - Wikidata & German articles</title>
		<!-- META -->
		<meta charset="UTF-8">
		<!-- CSS -->
		<link rel="stylesheet" href="docs/style.css">
	</head>

	<body class="">
		<div>
			<h1><span class="blue">Der</span><span class="red">Die</span><span class="green">Das</span></h1>
			<h2>Practice your German articles with Wikidata</h2>
			
		</div>
<?php
?>
			<div>
<?php 
	//end of the game, display total points
	if($phase=='end')
	{ 
	echo '<p>Thanks for playing!</p>
	<p id="score"> Your score is: '.$_SESSION[$langcode."points"].'</p>
	<div>
		<form method="post" action="index.php" class="form">
			<input type="submit" value="Play again" class="button"/>
		</form>
</div>
	';
	}
	//core of the game: displays a noun and a form to select the article
	else { echo '
<div>
		<p>What is the correct article?</p>
		
</div>
<div class="form">
		<form method="post" action="index.php">
		<span class="radio der">
			<input type="radio" id="der" name="article" value="der" /> <label for="der">der</label>
		</span>
		<span class="radio die">
			<input type="radio" id="die" name="article" value="die" /> <label for="die">die</label>
		</span>
		<span class="radio das">
			<input type="radio" id="das" name="article" value="das" /> <label for="das">das</label>
		</span>
		<span class="radio idk">
		<input type="radio" id="idk" name="article" value="idk" checked="checked"/> <label for="idk">I don\'t know</label>
		</span>
		<p id="noun">
			'.$_SESSION[$langcode."noun"].'
		</p>
		<input type="submit" value="Submit" class="button"/>
	</form>
</div>
	
<div>
			<p><span class="metrics round">Round: '.$_SESSION[$langcode."round"].'/10</span>
			<span class="metrics points">Points: '.$_SESSION[$langcode."points"].'</span>';
			}
?>
			</div>
			<div>
				<p class="previously">Previously:</p>
				<div class="">
					<?php echo $_SESSION[$langcode."list"]; //list of the previous nouns played ?>
				</div>
			
			
			</div>
		<div class="footer">
			<p id="footer">DerDieDas v1 by <a href="http://auregann.fr">Auregann</a>, last update on 28.12.2018 ~ Source of the data: <a href="https://wikidata.org">Wikidata</a> (<a href="https://query.wikidata.org/#SELECT%20%3Flemma%20%28SAMPLE%28%3Fgender%29%20AS%20%3Fgender%29%20WITH%20%7B%0A%20%20SELECT%20DISTINCT%20%3Flemma%20WHERE%20%7B%0A%20%20%20%20VALUES%20%3Fgender%20%7B%20wd%3AQ499327%20wd%3AQ1775415%20wd%3AQ1775461%20%7D%0A%20%20%20%20%3Flexeme%20dct%3Alanguage%20wd%3AQ188%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20wikibase%3AlexicalCategory%20wd%3AQ1084%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20wdt%3AP5185%20%3Fgender%3B%0A%20%20%20%20%20%20%20%20%20%20%20%20wikibase%3Alemma%20%3Flemma.%0A%20%20%7D%0A%20%20ORDER%20BY%20CONCAT%28MD5%28%3Flemma%29%2C%20STR%28NOW%28%29%29%29%0A%7D%20AS%20%25randomLemmas%20WHERE%20%7B%0A%20%20INCLUDE%20%25randomLemmas.%0A%20%20%3Flexeme%20wikibase%3Alemma%20%3Flemma%3B%0A%20%20%20%20%20%20%20%20%20%20wdt%3AP5185%20%3Fgender.%0A%7D%0AGROUP%20BY%20%3Flemma%0AHAVING%28COUNT%28%3Fgender%29%20%3D%201%29">query</a>) 
			~ <a href="https://github.com/Auregann">Github</a> ~ Special thanks to Lucas for the queries and Vigneron for the support ~ <a href="https://www.wikidata.org/wiki/Wikidata:Sixth_Birthday">Happy 6th birthday Wikidata!</a></p>
		</div>
	</body>
	
</html>
<?php 
	if($phase=='end')
	{
		session_unset();
			$_SESSION[$langcode."guess"]='';
			$_SESSION[$langcode."answer"]='';
			$_SESSION[$langcode."noun"]='';
			$_SESSION[$langcode."article"]='';
	}
?>
